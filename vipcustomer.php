<?php
define('ACCESS', true);

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập để xem thông tin VIP!");
}
$user_id = $_SESSION['user_id'];

// Cấu hình ghi log lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Hàm định dạng tiền tệ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}

// Tính tổng giá trị đơn hàng đã giao cho người dùng hiện tại
$stmt = $conn->prepare("
    SELECT u.full_name, u.email, SUM(o.total_amount) as total_spent
    FROM tbl_orders o
    JOIN tbl_users u ON o.user_id = u.user_id
    WHERE o.user_id = ? AND o.status = 'delivered'
    GROUP BY o.user_id
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$customer = $result->fetch_assoc();
$total_spent = $customer ? $customer['total_spent'] : 0;
$vip_level = '';

if ($total_spent >= 100000000) {
    $vip_level = 'Lục Bảo';
} elseif ($total_spent >= 70000000) {
    $vip_level = 'Kim Cương';
} elseif ($total_spent >= 40000000) {
    $vip_level = 'Vàng';
} elseif ($total_spent >= 20000000) {
    $vip_level = 'Bạc';
} elseif ($total_spent >= 5000000) {
    $vip_level = 'Đồng';
}

if ($vip_level) {
    // Lưu hoặc cập nhật thông tin vào bảng tbl_vip_customers
    $stmt_update = $conn->prepare("
        INSERT INTO tbl_vip_customers (user_id, total_spent, vip_level, updated_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE total_spent = ?, vip_level = ?
    ");
    $stmt_update->bind_param("idss", $user_id, $total_spent, $vip_level, $total_spent, $vip_level);
    if (!$stmt_update->execute()) {
        file_put_contents('php_errors.log', "Database Error: Failed to update tbl_vip_customers for user_id $user_id\n", FILE_APPEND);
    }
    $stmt_update->close();
}

$stmt->close();

// Định nghĩa khuyến mãi và ngưỡng cho progress bar
$promotions = [
    'Đồng' => ['discount' => 'Giảm 3% cho đơn hàng tiếp theo (tối đa 300,000 VND)', 'threshold' => 5000000],
    'Bạc' => ['discount' => 'Giảm 5% cho đơn hàng tiếp theo (tối đa 700,000 VND)', 'threshold' => 20000000],
    'Vàng' => ['discount' => 'Giảm 7% cho đơn hàng tiếp theo (tối đa 1,200,000 VND) và miễn phí vận chuyển cho 3 đơn hàng tiếp theo', 'threshold' => 40000000],
    'Kim Cương' => ['discount' => 'Giảm 10% cho đơn hàng tiếp theo (tối đa 2,500,000 VND) và ưu tiên giảm giá thêm 2% nếu mua điện thoại flagship (giá ≥ 20 triệu)', 'threshold' => 70000000],
    'Lục Bảo' => ['discount' => 'Giảm 15% cho đơn hàng tiếp theo (tối đa 5,000,000 VND) và quyền tham gia chương trình trả góp 0% lãi suất cho 1 lần mua sắm', 'threshold' => 100000000]
];

$next_threshold = 100000000; // Ngưỡng tối đa là Lục Bảo
$icon = '⚪';
$bar_color = '#cd7f32'; // Mặc định Đồng

if ($vip_level === 'Lục Bảo') {
    $next_threshold = null;
    $icon = '💚';
    $bar_color = '#50c878';
} elseif ($vip_level === 'Kim Cương') {
    $next_threshold = 100000000;
    $icon = '💎';
    $bar_color = '#b9f2ff';
} elseif ($vip_level === 'Vàng') {
    $next_threshold = 70000000;
    $icon = '⭐';
    $bar_color = '#ffd700';
} elseif ($vip_level === 'Bạc') {
    $next_threshold = 40000000;
    $icon = '⚪';
    $bar_color = '#c0c0c0';
} elseif ($vip_level === 'Đồng' || !$vip_level) {
    $next_threshold = 20000000;
    $icon = '⚪';
    $bar_color = '#cd7f32';
}

$progress = $next_threshold ? min(100, ($total_spent / $next_threshold) * 100) : 100;

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thông tin VIP của bạn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background-color: #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
        }
        .progress {
            height: 100%;
            background-color: <?php echo $bar_color; ?>;
            text-align: right;
            color: white;
            padding-right: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .vip-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Thông tin VIP của bạn</h2>
        <?php if ($total_spent > 0 || $vip_level): ?>
            <div class="text-center mb-6">
                <span class="vip-icon"><?php echo $icon; ?></span>
                <span class="text-xl font-semibold"><?php echo $vip_level ?: 'Chưa đạt VIP'; ?></span>
            </div>
            <div class="mb-4">
                <p><strong>Tên khách hàng:</strong> <?php echo htmlspecialchars($customer['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                <p><strong>Bạn đã tiêu:</strong> <?php echo formatCurrency($total_spent); ?></p>
                <p><strong>Khuyến mãi:</strong> <?php echo htmlspecialchars($promotions[$vip_level]['discount'] ?? 'Chưa có khuyến mãi'); ?></p>
            </div>
            <div class="mb-4">
                <p class="text-center">Tiến độ đến cấp bậc tiếp theo:</p>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $progress; ?>%;">
                        <?php echo $next_threshold ? number_format($progress, 1) . '%' : 'Đã đạt tối đa'; ?>
                    </div>
                </div>
                <?php if ($next_threshold): ?>
                    <p class="text-center text-sm text-gray-600 mt-2">
                        Cần thêm <?php echo formatCurrency($next_threshold - $total_spent); ?> để đạt <?php echo array_search($next_threshold, array_column($promotions, 'threshold')); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-600">Bạn chưa có đơn hàng nào với trạng thái đã giao.</p>
        <?php endif; ?>
    </div>
</body>
</html>