<?php
session_start();
require_once 'vendor/autoload.php'; // Nạp thư viện Composer
use GuzzleHttp\Client;

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");

// Hàm gọi API Gemini với Guzzle 6.2
function callGeminiAPI($prompt, $apiKey) {
    $client = new Client();
    $models = ['gemini-1.5-flash', 'gemini-1.5-pro']; // Fallback mô hình
    $lastError = null;

    foreach ($models as $model) {
        try {
            $response = $client->post("https://generativelanguage.googleapis.com/v1/models/$model:generateContent", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => ['key' => $apiKey],
                'body' => json_encode([
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ]
                ])
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Không nhận được phản hồi từ Gemini API.";
        } catch (Exception $e) {
            $lastError = "Lỗi khi gọi Gemini API ($model): " . $e->getMessage();
        }
    }
    return $lastError ?? "Lỗi khi gọi Gemini API: Không thể kết nối với bất kỳ mô hình nào.";
}

// Hàm lấy lịch sử trò chuyện gần nhất
function getRecentChatHistory($user_id, $conn, $limit = 3) {
    $sql = "SELECT user_message, bot_reply 
            FROM tbl_chatbot 
            WHERE user_id = ? AND status = 'success' 
            ORDER BY created_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = "Người dùng: " . $row['user_message'] . "\nBot: " . strip_tags($row['bot_reply']);
    }
    $stmt->close();
    return implode("\n---\n", array_reverse($history));
}

// Hàm tìm kiếm sản phẩm dựa trên embedding
function searchProducts($query, $conn) {
    $embeddings = json_decode(file_get_contents('product_embeddings.json'), true);
    if (!$embeddings) {
        return "Không thể đọc dữ liệu embedding.";
    }

    $queryEmbedding = getQueryEmbedding($query);
    if (!$queryEmbedding) {
        return "Không thể tạo embedding cho truy vấn.";
    }

    $results = [];
    foreach ($embeddings as $product) {
        $similarity = cosineSimilarity($queryEmbedding, $product['embedding']);
        $results[] = ['product_id' => $product['product_id'], 'similarity' => $similarity];
    }

    usort($results, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });

    $topProducts = array_slice($results, 0, 3);
    $productIds = array_column($topProducts, 'product_id');

    if (empty($productIds)) {
        return "Không tìm thấy sản phẩm phù hợp.";
    }

    $ids = implode(',', $productIds);
    $sql = "SELECT p.product_id, p.product_name, p.description, MIN(v.price) as min_price,
                   (SELECT image_url FROM tbl_product_images pi WHERE pi.product_id = p.product_id LIMIT 1) as image_url
            FROM tbl_products p
            JOIN tbl_product_variants v ON p.product_id = v.product_id
            WHERE p.product_id IN ($ids)
            GROUP BY p.product_id
            ORDER BY min_price ASC";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        return "Không tìm thấy sản phẩm nào phù hợp với yêu cầu.";
    }

    $products = [];
    $html = '<div class="consultation-text">Dưới đây là một số điện thoại giá rẻ phù hợp với nhu cầu chơi game của bạn:</div>';
    $html .= '<div class="product-list">';
    while ($row = $result->fetch_assoc()) {
        $image_url = strpos($row['image_url'], 'Uploads/') === false 
            ? 'Uploads/' . ltrim($row['image_url'], '/') 
            : $row['image_url'];
        $products[] = [
            'product' => $row['product_name'],
            'features' => "pin {$row['description']}, giá " . number_format($row['min_price'], 0, ',', '.') . " VNĐ"
        ];
        $html .= '<div class="product-item">';
        $html .= '<a class="sanpham" href="index.php?product_id=' . $row['product_id'] . '">';
        $html .= '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
        $html .= '<div>' . htmlspecialchars($row['product_name']) . '<br>' . number_format($row['min_price'], 0, ',', '.') . ' VNĐ</div>';
        $html .= '</a></div>';
    }
    $html .= '</div>';

    return ['html' => $html, 'products' => $products];
}

// Hàm tính cosine similarity
function cosineSimilarity($vec1, $vec2) {
    $dotProduct = 0.0;
    $normA = 0.0;
    $normB = 0.0;
    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $normA += $vec1[$i] * $vec1[$i];
        $normB += $vec2[$i] * $vec2[$i];
    }
    $normA = sqrt($normA);
    $normB = sqrt($normB);
    if ($normA == 0 || $normB == 0) {
        return 0.0;
    }
    return $dotProduct / ($normA * $normB);
}

// Hàm tạo embedding cho truy vấn
function getQueryEmbedding($query) {
    $query = escapeshellarg($query);
    $command = "python generate_embedding.py $query";
    $output = shell_exec($command);
    return json_decode($output, true);
}

// Hàm lấy mã giảm giá với giao diện HTML
function getPromotions($conn) {
    $sql = "SELECT promo_code, discount_value, discount_type, start_date, end_date
            FROM tbl_promotions
            WHERE status = 'active' AND end_date >= CURDATE()";
    $result = $conn->query($sql);

    $html = '<div class="consultation-text">Danh sách mã giảm giá hiện có:</div>';
    $html .= '<table class="promotion-table">';
    $html .= '<thead><tr><th>Mã giảm giá</th><th>Giảm giá</th><th>Hiệu lực</th><th>Hành động</th></tr></thead>';
    $html .= '<tbody>';

    while ($row = $result->fetch_assoc()) {
        $discount = $row['discount_type'] == 'percentage' 
            ? number_format($row['discount_value'], 0) . '%'
            : number_format($row['discount_value'], 0, ',', '.') . ' VNĐ';
        $validity = date('d/m/Y', strtotime($row['start_date'])) . ' - ' . date('d/m/Y', strtotime($row['end_date']));
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['promo_code']) . '</td>';
        $html .= '<td>' . htmlspecialchars($discount) . '</td>';
        $html .= '<td>' . htmlspecialchars($validity) . '</td>';
        $html .= '<td><span class="copy-btn" data-code="' . htmlspecialchars($row['promo_code']) . '">Copy</span></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}

// Hàm lấy sản phẩm flashsale
function getFlashSales($conn, $excludeIds = []) {
    $exclude = !empty($excludeIds) ? "AND p.product_id NOT IN (" . implode(',', $excludeIds) . ")" : "";
    $sql = "SELECT p.product_id, p.product_name, MIN(v.price) as min_price,
                   (SELECT image_url FROM tbl_product_images pi WHERE pi.product_id = p.product_id LIMIT 1) as image_url
            FROM tbl_flashsale fs
            JOIN tbl_products p ON fs.product_id = p.product_id
            JOIN tbl_product_variants v ON p.product_id = v.product_id
            JOIN tbl_flashsales f ON fs.flashsale_id = f.flashsale_id
            WHERE f.end_time >= NOW() $exclude
            GROUP BY p.product_id
            ORDER BY min_price ASC
            LIMIT 3";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        return "Hiện tại không có sản phẩm flashsale nào.";
    }

    $html = '<div class="consultation-text">Danh sách sản phẩm đang Flashsale:</div>';
    $html .= '<div class="product-list">';
    while ($row = $result->fetch_assoc()) {
        $image_url = strpos($row['image_url'], 'Uploads/') === false 
            ? 'Uploads/' . ltrim($row['image_url'], '/') 
            : $row['image_url'];
        $html .= '<div class="product-item">';
        $html .= '<a href="index.php?product_id=' . $row['product_id'] . '">';
        $html .= '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
        $html .= '<div>' . htmlspecialchars($row['product_name']) . '<br>' . number_format($row['min_price'], 0, ',', '.') . ' VNĐ</div>';
        $html .= '</a></div>';
    }
    $html .= '</div>';
    return $html;
}

// Xử lý yêu cầu AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $query = trim($data['query'] ?? '');
    $user_id = $data['user_id'] ?? null;
    $exclude_ids = $data['exclude_ids'] ?? [];
    $action = $data['action'] ?? '';

    if ($action === 'load_history' && $user_id) {
        $sql = "SELECT user_message, bot_reply FROM tbl_chatbot WHERE user_id = ? AND status = 'success' ORDER BY created_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        echo json_encode(['history' => $history]);
        exit;
    }

    if (!$query || !$user_id) {
        echo json_encode(['error' => 'Thiếu truy vấn hoặc user_id']);
        exit;
    }

    $response = '';
    if (stripos($query, 'mã giảm giá') !== false || stripos($query, 'khuyến mãi') !== false) {
        $response = getPromotions($conn);
    } elseif (stripos($query, 'flashsale') !== false || stripos($query, 'sản phẩm giảm giá') !== false) {
        $response = getFlashSales($conn, $exclude_ids);
    } elseif (stripos($query, 'đặt hàng') !== false) {
        $response = 'Để đặt hàng, bạn có thể làm theo các bước sau:<br>1. Chọn sản phẩm bạn muốn mua trên trang web.<br>2. Thêm sản phẩm vào giỏ hàng.<br>3. Vào giỏ hàng, kiểm tra sản phẩm và nhấn "Thanh toán".<br>4. Điền thông tin giao hàng và chọn phương thức thanh toán.<br>5. Xác nhận đơn hàng và chờ giao hàng.<br>Nếu cần hỗ trợ thêm, bạn có thể liên hệ qua Zalo: 0835512896.';
    } elseif (stripos($query, 'liên hệ') !== false) {
        $response = 'Bạn có thể liên hệ với chúng tôi qua:<br>- Zalo: 0835512896<br>- Email: support@phoneshop.com<br>- Hoặc để lại tin nhắn tại đây, chúng tôi sẽ phản hồi sớm nhất!';
    } elseif (stripos($query, 'điện thoại') !== false || stripos($query, 'smartphone') !== false) {
        $searchResult = searchProducts($query, $conn);
        if (is_string($searchResult)) {
            $response = $searchResult;
        } else {
            $products = $searchResult['products'];
            $html = $searchResult['html'];

            $productText = "";
            foreach ($products as $index => $product) {
                $productText .= ($index + 1) . ". {$product['product']}: {$product['features']}.\n";
            }
            $chatHistory = getRecentChatHistory($user_id, $conn, 3);
            $prompt = "Lịch sử trò chuyện gần nhất:\n$chatHistory\n---\nNgười dùng hỏi: $query\n\nDưới đây là một số sản phẩm phù hợp:\n$productText\nDựa vào thông tin trên, hãy trả lời ngắn gọn, tự nhiên, thân thiện như một nhân viên bán hàng. Nếu người dùng hỏi về 'giá rẻ', hãy đề xuất các sản phẩm có giá hợp lý trong phân khúc (dưới 8 triệu), tập trung vào hiệu năng chơi game (chip mạnh, màn hình mượt, pin trâu).";
            
            $llmResponse = callGeminiAPI($prompt, 'AIzaSyAvXWE6BtTv_4pLi3cDsiU-0FifYpgiwrs');   
            $response = '<div class="llm-response">' . htmlspecialchars($llmResponse) . '</div>' . $html;
        }
    } else {
        $chatHistory = getRecentChatHistory($user_id, $conn, 3);
        $prompt = "Lịch sử trò chuyện gần nhất:\n$chatHistory\n---\nNgười dùng hỏi: $query\n\nHãy trả lời ngắn gọn, tự nhiên, thân thiện như một nhân viên bán hàng.";
        $response = callGeminiAPI($prompt, 'AIzaSyAvXWE6BtTv_4pLi3cDsiU-0FifYpgiwrs');
    }

    $sql = "INSERT INTO tbl_chatbot (user_id, user_message, bot_reply, sender_type, status) VALUES (?, ?, ?, 'bot', 'success')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $query, $response);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['response' => $response]);
}

$conn->close();
?>