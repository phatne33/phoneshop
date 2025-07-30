<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$user_id || $user_id <= 0) {
    header("Location: login.php");
    exit();
}

$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
        .success-message {
            background: #c6f6d5;
            color: #276749;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            background: #fed7d7;
            color: #9b2c2c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .order-products {
            flex-wrap: nowrap;
            gap: 15px;
            overflow-x: auto;
            margin-top: 10px;
            padding: 10px 0;
        }
        .product-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            background: #fff;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        .product-item img {
            width: 60px;
            height: auto;
            border-radius: 4px;
        }
        @media (max-width: 768px) {
            .product-item { min-width: 250px; }
            .product-item img { width: 50px; }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="header text-center mb-4" style="font-size: 28px; font-weight: bold; color: #2d3748;">Đơn Hàng Của Tôi</h1>

        <!-- Thông báo -->
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo '<p class="success-message">Đơn hàng đã được hủy thành công!</p>';
        }
        if (isset($_GET['error'])) {
            echo '<p class="error-message">' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
        ?>

        <div class="order-list">
            <?php
            $sql_orders = "SELECT o.*, a.address_detail, a.recipient_phone, a.hamlet, a.commune, a.district, a.province 
                           FROM tbl_orders o 
                           LEFT JOIN tbl_addresses a ON o.address_id = a.address_id 
                           WHERE o.user_id = ? 
                           ORDER BY o.order_date DESC";
            $stmt_orders = $conn->prepare($sql_orders);
            $stmt_orders->bind_param("i", $user_id);
            $stmt_orders->execute();
            $result_orders = $stmt_orders->get_result();

            if ($result_orders->num_rows > 0) {
                while ($row = $result_orders->fetch_assoc()) {
                    // Chuyển mã code thành tên chữ bằng Province Open API
                    $province_name = $row['province'] ?? 'Không xác định';
                    $district_name = $row['district'] ?? 'Không xác định';
                    $commune_name = $row['commune'] ?? 'Không xác định';

                    // Loại bỏ số 0 dẫn đầu trong mã code
                    $province_code = !empty($row['province']) ? ltrim($row['province'], '0') : '';
                    $district_code = !empty($row['district']) ? ltrim($row['district'], '0') : '';
                    $commune_code = !empty($row['commune']) ? ltrim($row['commune'], '0') : '';

                    // Lấy tên tỉnh
                    if ($province_code) {
                        $province_data = @file_get_contents("https://provinces.open-api.vn/api/p/{$province_code}");
                        if ($province_data !== false) {
                            $province_json = json_decode($province_data, true);
                            $province_name = $province_json['name'] ?? $row['province'];
                        } else {
                            error_log("Province API failed for code: {$province_code}");
                        }
                    }

                    // Lấy tên huyện
                    if ($district_code) {
                        $district_data = @file_get_contents("https://provinces.open-api.vn/api/d/{$district_code}");
                        if ($district_data !== false) {
                            $district_json = json_decode($district_data, true);
                            $district_name = $district_json['name'] ?? $row['district'];
                        } else {
                            error_log("District API failed for code: {$district_code}");
                        }
                    }

                    // Lấy tên xã/phường (ward)
                    if ($commune_code) {
                        $commune_data = @file_get_contents("https://provinces.open-api.vn/api/w/{$commune_code}");
                        error_log("Ward API request for code {$commune_code}: " . ($commune_data === false ? 'Failed' : $commune_data));
                        if ($commune_data !== false) {
                            $commune_json = json_decode($commune_data, true);
                            $commune_name = $commune_json['name'] ?? $row['commune'];
                        }
                    }

                    // Tạo chuỗi địa chỉ đầy đủ với tên chữ
                    $full_address = htmlspecialchars(
                        ($row['address_detail'] ?? 'Không có') . ', ' .
                        ($row['hamlet'] ? $row['hamlet'] . ', ' : '') .
                        $commune_name . ', ' .
                        $district_name . ', ' .
                        $province_name,
                        ENT_QUOTES, 'UTF-8'
                    );

                    echo '<div class="card mb-3 order-item" style="border: 1px solid #e2e8f0; padding: 15px; background: #fafafa; transition: box-shadow 0.3s ease;">';
                    echo '<div class="card-body">';
                    echo '<div class="d-flex justify-content-between align-items-center mb-3 order-header">';
                    echo '<span class="order-id" style="font-weight: bold; color: #1a202c;">Mã đơn hàng: #' . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . '</span>';
                    echo '<span class="badge ' . getStatusClass($row['status']) . '" style="padding: 5px 10px; border-radius: 12px; font-size: 14px; font-weight: 500; text-transform: capitalize;">' . ucfirst(htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8')) . '</span>';
                    echo '</div>';
                    echo '<div class="order-details">';
                    echo '<p><strong style="color: #2d3748;">Tổng tiền:</strong> ' . number_format($row['total_amount'], 0, ',', '.') . ' VNĐ</p>';
                    echo '<p><strong style="color: #2d3748;">Phương thức thanh toán:</strong> ' . htmlspecialchars($row['payment_method'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p><strong style="color: #2d3748;">Phương thức vận chuyển:</strong> ' . htmlspecialchars($row['shipping_method'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p><strong style="color: #2d3748;">Địa chỉ giao hàng:</strong> ' . $full_address . '</p>';
                    echo '<p><strong style="color: #2d3748;">Số điện thoại:</strong> ' . htmlspecialchars($row['recipient_phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p><strong style="color: #2d3748;">Ghi chú:</strong> ' . ($row['notes'] ? htmlspecialchars($row['notes'], ENT_QUOTES, 'UTF-8') : 'Không có') . '</p>';
                    echo '<p><strong style="color: #2d3748;">Ngày đặt hàng:</strong> ' . date('d/m/Y H:i', strtotime($row['order_date'])) . '</p>';
                    echo '</div>';

                    // Hiển thị sản phẩm trong đơn hàng
                    echo '<div class="order-products">';
                    $sql_products = "SELECT od.*, p.product_name, pi.image_url, pc.color_name, pv.storage 
                                     FROM tbl_order_details od 
                                     LEFT JOIN tbl_products p ON od.product_id = p.product_id 
                                     LEFT JOIN tbl_product_images pi ON od.product_id = pi.product_id 
                                     LEFT JOIN tbl_product_colors pc ON od.color_id = pc.color_id 
                                     LEFT JOIN tbl_product_variants pv ON od.product_id = pv.product_id AND od.storage = pv.storage 
                                     WHERE od.order_id = ? LIMIT 1";
                    $stmt_products = $conn->prepare($sql_products);
                    $stmt_products->bind_param("i", $row['order_id']);
                    $stmt_products->execute();
                    $result_products = $stmt_products->get_result();

                    while ($product = $result_products->fetch_assoc()) {
                        $image_path = $product['image_url'] ? 'Uploads/' . htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') : 'Uploads/default-image.jpg';

                        echo '<div class="product-item">';
                        echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<div class="product-info">';
                        echo '<p><strong>' . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . '</strong></p>';
                        echo '<p>Màu: ' . ($product['color_name'] ? htmlspecialchars($product['color_name'], ENT_QUOTES, 'UTF-8') : 'N/A') . '</p>';
                        echo '<p>Dung lượng: ' . ($product['storage'] ? htmlspecialchars($product['storage'], ENT_QUOTES, 'UTF-8') : 'N/A') . '</p>';
                        echo '<p>Số lượng: ' . htmlspecialchars($product['quantity'], ENT_QUOTES, 'UTF-8') . ' - Giá: ' . number_format($product['unit_price'], 0, ',', '.') . ' VNĐ</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    $stmt_products->close();
                    echo '</div>';

                    // Nút hủy đơn hàng
                    if ($row['status'] === 'pending') {
                        echo '<form method="POST" action="cancel_order.php" onsubmit="return confirm(\'Bạn có chắc muốn hủy đơn hàng này không?\');">';
                        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<input type="hidden" name="cancel_order_id" value="' . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<button type="submit" class="btn btn-danger cancel-btn" style="padding: 8px 15px; font-size: 14px;">Hủy Đơn Hàng</button>';
                        echo '</form>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center text-muted no-orders" style="padding: 20px; font-size: 16px;">Bạn chưa có đơn hàng nào.</p>';
            }
            $stmt_orders->close();
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>

<?php
function getStatusClass($status) {
    $statusClasses = [
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-info text-dark',
        'processing' => 'bg-primary text-white',
        'shipping' => 'bg-purple text-white',
        'delivered' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white'
    ];
    return $statusClasses[$status] ?? 'bg-secondary text-white';
}
?>