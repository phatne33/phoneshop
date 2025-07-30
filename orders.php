<?php
session_start();
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    $sql_cart = "SELECT c.*, p.product_name, p.product_id, pv.price AS variant_price, pv.storage, pc.color_name, pi.image_url 
                 FROM tbl_cart c 
                 LEFT JOIN tbl_products p ON c.product_id = p.product_id 
                 LEFT JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage 
                 LEFT JOIN tbl_product_colors pc ON c.product_id = pc.product_id AND c.color = pc.color_name 
                 LEFT JOIN tbl_product_images pi ON c.product_id = pi.product_id AND pc.color_id = pi.color_id 
                 WHERE c.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    $cart_items = [];
    $total = 0;
    while ($row = $result_cart->fetch_assoc()) {
        $price = $row['variant_price'] ?? 0;
        if ($price == 0) {
            $price = $row['price'] ?? 0;
        }
        $subtotal = $price * $row['quantity'];
        $total += $subtotal;
        $cart_items[] = array_merge($row, ['subtotal' => $subtotal]);
    }

    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng của bạn trống.']);
        exit();
    }

    $discount = 0;
    $promo_code = '';
    $original_total = $total;
    if (isset($_SESSION['promo_code']) && !empty($_SESSION['promo_code'])) {
        $promo_code = $_SESSION['promo_code'];
        $promo_sql = "SELECT * FROM tbl_promotions WHERE promo_code = ? AND status = 'active' AND start_date <= NOW() AND end_date >= NOW()";
        $promo_stmt = $conn->prepare($promo_sql);
        $promo_stmt->bind_param("s", $promo_code);
        $promo_stmt->execute();
        $promo_result = $promo_stmt->get_result();

        if ($promo_result->num_rows > 0) {
            $promo = $promo_result->fetch_assoc();
            $_SESSION['discount_type'] = $promo['discount_type'];
            if ($promo['discount_type'] === 'percentage') {
                $discount = $promo['discount_value'] / 100;
                $total = $total * (1 - $discount);
                $discount = $original_total * $discount;
            } else {
                $discount = $promo['discount_value'];
                $total = max(0, $total - $discount);
            }
        } else {
            unset($_SESSION['promo_code']);
            unset($_SESSION['discount']);
            unset($_SESSION['discount_type']);
        }
        $promo_stmt->close();
    }

    $shipping_fee = ($total >= 5000000) ? 0 : 30000;
    $total_with_shipping = $total + $shipping_fee;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $address_id = $_POST['address_id'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $shipping_method = $_POST['shipping_method'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if (empty($address_id) || empty($payment_method) || empty($shipping_method)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
            exit();
        }

        $address_check_sql = "SELECT address_id FROM tbl_addresses WHERE address_id = ? AND user_id = ?";
        $address_check_stmt = $conn->prepare($address_check_sql);
        $address_check_stmt->bind_param("ii", $address_id, $user_id);
        $address_check_stmt->execute();
        $address_check_result = $address_check_stmt->get_result();
        if ($address_check_result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Địa chỉ không hợp lệ.']);
            exit();
        }
        $address_check_stmt->close();

        $sql_order = "INSERT INTO tbl_orders (user_id, address_id, total_amount, payment_method, shipping_method, status, notes, promo_code) 
                      VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        $total_amount = $total_with_shipping;
        $stmt_order->bind_param("iisssss", $user_id, $address_id, $total_amount, $payment_method, $shipping_method, $notes, $promo_code);
        if (!$stmt_order->execute()) {
            throw new Exception('Lỗi khi tạo đơn hàng: ' . $stmt_order->error);
        }
        $order_id = $conn->insert_id;
        $created_at = date('Y-m-d H:i:s');

        $sql_order_details = "INSERT INTO tbl_order_details (order_id, product_id, quantity, unit_price, storage, color_id) 
                              VALUES (?, ?, ?, ?, ?, (SELECT color_id FROM tbl_product_colors WHERE product_id = ? AND color_name = ? LIMIT 1))";
        $stmt_order_details = $conn->prepare($sql_order_details);
        foreach ($cart_items as $item) {
            $check_color_sql = "SELECT COUNT(*) as count, GROUP_CONCAT(color_id) as color_ids 
                                FROM tbl_product_colors 
                                WHERE product_id = ? AND color_name = ?";
            $check_color_stmt = $conn->prepare($check_color_sql);
            $check_color_stmt->bind_param("is", $item['product_id'], $item['color']);
            $check_color_stmt->execute();
            $check_color_result = $check_color_stmt->get_result();
            $color_data = $check_color_result->fetch_assoc();
            $color_count = $color_data['count'];
            $check_color_stmt->close();

            if ($color_count > 1) {
                error_log("Trùng lặp màu cho product_id: {$item['product_id']}, color: {$item['color']}, color_ids: {$color_data['color_ids']}");
                throw new Exception("Lỗi dữ liệu: Nhiều màu trùng lặp cho sản phẩm ID {$item['product_id']} và màu {$item['color']}.");
            }

            $stmt_order_details->bind_param("iiidssi", $order_id, $item['product_id'], $item['quantity'], $item['variant_price'], $item['storage'], $item['product_id'], $item['color']);
            if (!$stmt_order_details->execute()) {
                throw new Exception('Lỗi khi lưu chi tiết đơn hàng: ' . $stmt_order_details->error);
            }
        }

        $sql_delete_cart = "DELETE FROM tbl_cart WHERE user_id = ?";
        $stmt_delete_cart = $conn->prepare($sql_delete_cart);
        $stmt_delete_cart->bind_param("i", $user_id);
        if (!$stmt_delete_cart->execute()) {
            throw new Exception('Lỗi khi xóa giỏ hàng: ' . $stmt_delete_cart->error);
        }

        $conn->commit();

        $user_sql = "SELECT full_name, email FROM tbl_users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if (!$user_result->num_rows) {
            throw new Exception('Không tìm thấy thông tin khách hàng.');
        }
        $user = $user_result->fetch_assoc();
        $customer_name = $user['full_name'];
        $customer_email = $user['email'];
        $user_stmt->close();

        $address_sql = "SELECT recipient_name, recipient_phone, address_detail, province, district, commune, hamlet 
                        FROM tbl_addresses WHERE address_id = ?";
        $address_stmt = $conn->prepare($address_sql);
        $address_stmt->bind_param("i", $address_id);
        $address_stmt->execute();
        $address_result = $address_stmt->get_result();
        if (!$address_result->num_rows) {
            throw new Exception('Không tìm thấy thông tin địa chỉ.');
        }
        $address = $address_result->fetch_assoc();
        $full_address = "{$address['address_detail']}, {$address['commune']}, {$address['district']}, {$address['province']}" . 
                        ($address['hamlet'] ? ", {$address['hamlet']}" : "");
        $address_stmt->close();

        $order_items_sql = "SELECT od.product_id, od.quantity, od.unit_price, od.storage, pc.color_name, 
                                   p.product_name, pi.image_url
                            FROM tbl_order_details od
                            LEFT JOIN tbl_products p ON od.product_id = p.product_id
                            LEFT JOIN tbl_product_colors pc ON od.color_id = pc.color_id
                            LEFT JOIN tbl_product_images pi ON od.product_id = pi.product_id AND pc.color_id = pi.color_id
                            WHERE od.order_id = ?";
        $order_items_stmt = $conn->prepare($order_items_sql);
        $order_items_stmt->bind_param("i", $order_id);
        $order_items_stmt->execute();
        $order_items_result = $order_items_stmt->get_result();
        $order_items = [];
        while ($item = $order_items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
        $order_items_stmt->close();

        $order_details = [
            'order_id' => $order_id,
            'created_at' => $created_at,
            'customer_name' => $customer_name,
            'recipient_name' => $address['recipient_name'],
            'recipient_phone' => $address['recipient_phone'],
            'address' => $full_address,
            'payment_method' => $payment_method,
            'shipping_method' => $shipping_method,
            'notes' => $notes,
            'promo_code' => $promo_code,
            'promo_discount' => $discount,
            'shipping_fee' => $shipping_fee,
            'total_amount' => $total_with_shipping
        ];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kanekikentp@gmail.com';
            $mail->Password = 'zfrmwfnkxuyctgvm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8'; // Đặt CharSet thành UTF-8

            $mail->setFrom('kanekikentp@gmail.com', 'Phone Shop');
            $mail->addAddress($customer_email, $customer_name);
            $mail->isHTML(true);

            // Mã hóa tiêu đề email để hỗ trợ tiếng Việt
            $subject = "Xác nhận đơn hàng #$order_id từ Phone Shop";
            $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

            ob_start();
            ?>
            <!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Xác nhận đơn hàng</title>
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #1f2937; color: #ffffff; padding: 20px; text-align: center; }
                    .order-details { border: 1px solid #e5e7eb; padding: 15px; margin-bottom: 20px; }
                    .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .table th, .table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; background-color: #000000; color: #ffffff; }
                    .table th { font-weight: bold; }
                    .footer { text-align: center; padding: 20px; background-color: #f3f4f6; color: #333; }
                    img { max-width: 80px; height: auto; display: block; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1 style="font-family: Arial, Helvetica, sans-serif; font-size: 24px; font-weight: bold; margin: 0; color: #ffffff;">Xác nhận đơn hàng #<?php echo htmlspecialchars($order_details['order_id']); ?></h1>
                        <p style="font-family: Arial, Helvetica, sans-serif; margin: 5px 0; color: #ffffff;">Cảm ơn bạn đã mua sắm tại Phone Shop!</p>
                    </div>
                    <div class="order-details">
                        <h2 style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; font-weight: bold; margin-top: 0; color: #333;">Thông tin đơn hàng</h2>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order_details['order_id']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Người nhận:</strong> <?php echo htmlspecialchars($order_details['recipient_name']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order_details['recipient_phone']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Phương thức vận chuyển:</strong> <?php echo htmlspecialchars($order_details['shipping_method']); ?></p>
                        <?php if (!empty($order_details['notes'])): ?>
                            <p style="font-family: Arial, Helvetica, sans-serif;"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order_details['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <h2 style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; font-weight: bold; color: #333;">Sản phẩm trong đơn hàng</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="font-family: Arial, Helvetica, sans-serif;">Hình ảnh</th>
                                <th style="font-family: Arial, Helvetica, sans-serif;">Sản phẩm</th>
                                <th style="font-family: Arial, Helvetica, sans-serif;">Số lượng</th>
                                <th style="font-family: Arial, Helvetica, sans-serif;">Đơn giá</th>
                                <th style="font-family: Arial, Helvetica, sans-serif;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td style="font-family: Arial, Helvetica, sans-serif;"><img src="http://localhost/phone-shop/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                                    <td style="font-family: Arial, Helvetica, sans-serif;">
                                        <?php echo htmlspecialchars($item['product_name']); ?><br>
                                        <small>
                                            Dung lượng: <?php echo htmlspecialchars($item['storage']); ?><br>
                                            Màu: <?php echo htmlspecialchars($item['color_name']); ?>
                                        </small>
                                    </td>
                                    <td style="font-family: Arial, Helvetica, sans-serif;"><?php echo $item['quantity']; ?></td>
                                    <td style="font-family: Arial, Helvetica, sans-serif;"><?php echo number_format($item['unit_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                                    <td style="font-family: Arial, Helvetica, sans-serif;"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') . ' VNĐ'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align: right; margin-bottom: 20px; font-family: Arial, Helvetica, sans-serif;">
                        <p><strong>Tổng tiền hàng:</strong> <?php echo number_format($order_details['total_amount'] - $order_details['shipping_fee'] + $order_details['promo_discount'], 0, ',', '.') . ' VNĐ'; ?></p>
                        <?php if ($order_details['promo_discount'] > 0): ?>
                            <p><strong>Giảm giá (<?php echo htmlspecialchars($order_details['promo_code']); ?>):</strong> -<?php echo number_format($order_details['promo_discount'], 0, ',', '.') . ' VNĐ'; ?></p>
                        <?php endif; ?>
                        <p><strong>Phí vận chuyển:</strong> <?php echo number_format($order_details['shipping_fee'], 0, ',', '.') . ' VNĐ'; ?></p>
                        <p><strong>Tổng cộng:</strong> <?php echo number_format($order_details['total_amount'], 0, ',', '.') . ' VNĐ'; ?></p>
                    </div>
                    <div class="footer">
                        <p style="font-family: Arial, Helvetica, sans-serif;">Liên hệ với chúng tôi qua email: <a href="mailto:kanekikentp@gmail.com">kanekikentp@gmail.com</a></p>
                        <p style="font-family: Arial, Helvetica, sans-serif;">© <?php echo date('Y'); ?> Phone Shop. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            <?php
            $mail->Body = ob_get_clean();
            $mail->AltBody = strip_tags($mail->Body);

            $mail->send();
        } catch (Exception $e) {
            error_log("Gửi email thất bại cho đơn hàng #$order_id: {$mail->ErrorInfo}");
        }

        unset($_SESSION['promo_code']);
        unset($_SESSION['discount']);
        unset($_SESSION['discount_type']);

        session_regenerate_id(true);
        echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được đặt thành công!']);
        exit();
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

$address_sql = "SELECT * FROM tbl_addresses WHERE user_id = ?";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$addresses = [];
while ($row = $address_result->fetch_assoc()) {
    $addresses[] = $row;
}
$address_stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 max-w-4xl">
        <h1 class="text-2xl font-bold mb-4 text-center">Thanh toán</h1>

        <div class="bg-white p-4 rounded-lg shadow-md mb-4">
            <h2 class="text-xl font-semibold mb-2">Sản phẩm trong giỏ hàng</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="flex items-center mb-4">
                    <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="w-20 h-20 object-cover mr-4">
                    <div>
                        <h3 class="text-lg font-medium"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p class="text-gray-600">Dung lượng: <?php echo htmlspecialchars($item['storage']); ?></p>
                        <p class="text-gray-600">Màu: <?php echo htmlspecialchars($item['color_name']); ?></p>
                        <p class="text-gray-600">Số lượng: <?php echo $item['quantity']; ?></p>
                        <p class="text-gray-800 font-semibold"><?php echo number_format($item['subtotal'], 0, ',', '.') . ' VNĐ'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form id="checkout-form" method="POST" class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-2">Thông tin giao hàng</h2>
            <?php if (!empty($addresses)): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Chọn địa chỉ giao hàng:</label>
                    <select name="address_id" class="w-full p-2 border rounded" required>
                        <option value="">-- Chọn địa chỉ --</option>
                        <?php foreach ($addresses as $address): ?>
                            <option value="<?php echo $address['address_id']; ?>" <?php echo $address['is_default'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars("{$address['recipient_name']} - {$address['recipient_phone']} - {$address['address_detail']}, {$address['commune']}, {$address['district']}, {$address['province']}" . ($address['hamlet'] ? ", {$address['hamlet']}" : "")); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <p class="text-red-600">Vui lòng thêm địa chỉ giao hàng trong tài khoản của bạn.</p>
                <a href="profile.php" class="text-blue-600 hover:underline">Thêm địa chỉ</a>
            <?php endif; ?>

            <h2 class="text-xl font-semibold mb-2">Phương thức thanh toán</h2>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="payment_method" value="COD" checked class="form-radio">
                    <span class="ml-2">Thanh toán khi nhận hàng (COD)</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input type="radio" name="payment_method" value="Bank" class="form-radio">
                    <span class="ml-2">Chuyển khoản ngân hàng</span>
                </label>
            </div>

            <h2 class="text-xl font-semibold mb-2">Phương thức vận chuyển</h2>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="shipping_method" value="Standard" checked class="form-radio">
                    <span class="ml-2">Giao hàng tiêu chuẩn</span>
                </label>
            </div>

            <h2 class="text-xl font-semibold mb-2">Ghi chú</h2>
            <div class="mb-4">
                <textarea name="notes" class="w-full p-2 border rounded" rows="4" placeholder="Ghi chú cho đơn hàng (nếu có)"></textarea>
            </div>

            <div class="total-section py-2 text-center sm:text-right">
                <div class="total text-sm font-semibold text-gray-800 pb-1 sm:text-base md:text-lg">
                    Tổng tiền hàng: <?php echo number_format($original_total, 0, ',', '.') . ' VNĐ'; ?>
                </div>
                <?php if ($discount > 0 && !empty($promo_code)): ?>
                    <div class="discount text-sm font-semibold text-green-600 pb-1 sm:text-base md:text-lg">
                        Giảm giá (<?php echo htmlspecialchars($promo_code, ENT_QUOTES, 'UTF-8'); ?>): 
                        <?php echo isset($_SESSION['discount_type']) && $_SESSION['discount_type'] === 'percentage' 
                            ? ($_SESSION['discount'] * 100) . '%' 
                            : number_format($discount, 0, ',', '.') . ' VNĐ'; ?>
                    </div>
                <?php endif; ?>
                <div class="shipping-info text-sm font-semibold text-gray-800 pb-1 sm:text-base md:text-lg">
                    Phí vận chuyển: <?php echo number_format($shipping_fee, 0, ',', '.') . ' VNĐ'; ?>
                </div>
                <?php if ($total >= 5000000): ?>
                    <div class="freeship-note text-green-600 text-xs pb-1 sm:text-sm md:text-sm">Đơn hàng trên 5 triệu - Được miễn phí vận chuyển toàn quốc!</div>
                <?php endif; ?>
                <div class="total-with-shipping text-sm font-semibold text-red-600 pb-1 sm:text-base md:text-lg">
                    Tổng cộng: <?php echo number_format($total_with_shipping, 0, ',', '.') . ' VNĐ'; ?>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Đặt hàng</button>
        </form>
    </div>

    <script>
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'cart.php';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Đã xảy ra lỗi. Vui lòng thử lại.');
            });
        });
    </script>
</body>
</html>
<?php
$stmt_cart->close();
$conn->close();
?>