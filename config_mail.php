<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Nếu dùng Composer
// Hoặc: require 'vendor/PHPMailer/src/PHPMailer.php'; require 'vendor/PHPMailer/src/SMTP.php'; require 'vendor/PHPMailer/src/Exception.php';

function sendOrderConfirmationEmail($recipientEmail, $recipientName, $orderDetails, $orderItems, $shopName = "Phone Shop") {
    $mail = new PHPMailer(true);
    try {
        // Cấu hình server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kanekikentp@gmail.com'; // Thay bằng email của bạn
        $mail->Password = 'zfrmwfnkxuyctgvm'; // Thay bằng App Password của Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Thiết lập thông tin email
        $mail->setFrom('kanekikentp@gmail.com', $shopName);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = "Xác nhận đơn hàng #{$orderDetails['order_id']} từ {$shopName}";
        $mail->Body = generateOrderEmailTemplate($orderDetails, $orderItems, $shopName);
        $mail->AltBody = strip_tags($mail->Body); // Nội dung plain text cho client không hỗ trợ HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Gửi email thất bại: {$mail->ErrorInfo}");
        return false;
    }
}
function generateOrderEmailTemplate($orderDetails, $orderItems, $shopName) {
    $promoDiscount = isset($orderDetails['promo_discount']) ? $orderDetails['promo_discount'] : 0;
    $shippingFee = $orderDetails['shipping_fee'];
    $totalWithShipping = $orderDetails['total_amount'];

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Xác nhận đơn hàng</title>
        <style>
            /* Inline CSS tối thiểu để đảm bảo tương thích */
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1f2937; color: #fff; padding: 20px; text-align: center; }
            .order-details { border: 1px solid #e5e7eb; padding: 15px; margin-bottom: 20px; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .table th, .table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
            .table th { background-color: #f3f4f6; }
            .footer { text-align: center; padding: 20px; background-color: #f3f4f6; }
            .button { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #fff; text-decoration: none; border-radius: 5px; }
            img { max-width: 80px; height: auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="font-size: 24px; margin: 0;">Xác nhận đơn hàng #<?php echo htmlspecialchars($orderDetails['order_id']); ?></h1>
                <p style="margin: 5px 0;">Cảm ơn bạn đã mua sắm tại <?php echo htmlspecialchars($shopName); ?>!</p>
            </div>
            <div class="order-details">
                <h2 style="font-size: 18px; margin-top: 0;">Thông tin đơn hàng</h2>
                <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($orderDetails['order_id']); ?></p>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($orderDetails['created_at'])); ?></p>
                <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($orderDetails['customer_name']); ?></p>
                <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($orderDetails['address']); ?></p>
                <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($orderDetails['payment_method']); ?></p>
                <p><strong>Phương thức vận chuyển:</strong> <?php echo htmlspecialchars($orderDetails['shipping_method']); ?></p>
                <?php if (!empty($orderDetails['notes'])): ?>
                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($orderDetails['notes']); ?></p>
                <?php endif; ?>
            </div>
            <h2 style="font-size: 18px;">Sản phẩm trong đơn hàng</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                            <td>
                                <?php echo htmlspecialchars($item['product_name']); ?><br>
                                <small>
                                    Dung lượng: <?php echo htmlspecialchars($item['storage']); ?><br>
                                    Màu: <?php echo htmlspecialchars($item['color']); ?>
                                </small>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.') . ' VNĐ'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align: right; margin-bottom: 20px;">
                <p><strong>Tổng tiền hàng:</strong> <?php echo number_format($totalWithShipping - $shippingFee + $promoDiscount, 0, ',', '.') . ' VNĐ'; ?></p>
                <?php if ($promoDiscount > 0): ?>
                    <p><strong>Giảm giá (<?php echo htmlspecialchars($orderDetails['promo_code']); ?>):</strong> -<?php echo number_format($promoDiscount, 0, ',', '.') . ' VNĐ'; ?></p>
                <?php endif; ?>
                <p><strong>Phí vận chuyển:</strong> <?php echo number_format($shippingFee, 0, ',', '.') . ' VNĐ'; ?></p>
                <p><strong>Tổng cộng:</strong> <?php echo number_format($totalWithShipping, 0, ',', '.') . ' VNĐ'; ?></p>
            </div>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="https://your-domain.com/order-tracking.php?order_id=<?php echo $orderDetails['order_id']; ?>" class="button">Xem chi tiết đơn hàng</a>
            </div>
            <div class="footer">
                <p>Liên hệ với chúng tôi qua email: <a href="mailto:support@your-domain.com">support@your-domain.com</a></p>
                <p>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($shopName); ?>. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}