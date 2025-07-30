<?php
define('ACCESS', true);
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Khởi động session với bảo mật
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 24 giờ
        'cookie_secure' => true,    // Chỉ dùng trên HTTPS
        'cookie_httponly' => true,  // Ngăn JS truy cập cookie
        'use_strict_mode' => true   // Chống session fixation
]);}

// Tắt hiển thị lỗi chi tiết
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Xử lý đăng ký
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Yêu cầu không hợp lệ!";
    } else {
        $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

        // Xác thực đầu vào
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không hợp lệ!";
        } elseif (strlen($password) < 6) {
            $error = "Mật khẩu phải có ít nhất 6 ký tự!";
        } elseif (empty($full_name)) {
            $error = "Họ và tên không được để trống!";
        } else {
            // Kiểm tra email đã tồn tại chưa
            $stmt = $conn->prepare("SELECT email FROM tbl_users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email đã được sử dụng!";
            } else {
                // Mã hóa mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO tbl_users (full_name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, 'user', NOW())");
                $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);
                if ($stmt->execute()) {
                    $success = "Đăng ký thành công! Vui lòng <a href='login_customer.php'>đăng nhập</a>.";
                } else {
                    $error = "Đăng ký thất bại!";
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();

// Thêm header bảo mật
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng ký Khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng ký Khách hàng</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off" onsubmit="return validateForm()" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">Họ và tên</label>
                <input type="text" class="form-control mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500" id="full_name" name="full_name" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" class="form-control mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500" id="email" name="email" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative">
                    <input type="password" class="form-control mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500" id="password" name="password" required>
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword()">
                        <i class="fas fa-eye text-gray-500" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="text" class="form-control mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500" id="phone" name="phone">
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                <input type="text" class="form-control mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500" id="address" name="address">
            </div>
            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg hover:bg-gray-900 transition">Đăng ký</button>
        </form>
        <div class="text-center mt-4">
            <p class="text-sm text-gray-600">Đã có tài khoản? <a href="login_customer.php" class="text-blue-600 hover:underline">Đăng nhập ngay</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function validateForm() {
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const phone = document.getElementById('phone').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^[0-9]{10}$/;

            if (fullName === '') {
                alert('Họ và tên không được để trống!');
                return false;
            }
            if (!emailRegex.test(email)) {
                alert('Vui lòng nhập email hợp lệ!');
                return false;
            }
            if (password.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
            if (phone && !phoneRegex.test(phone)) {
                alert('Số điện thoại phải là 10 chữ số!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>