<?php
define('ACCESS', true);
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khởi động session với bảo mật
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Tắt cho localhost
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Ghi log lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Tạo CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Giới hạn số lần đăng nhập thất bại
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

// Kiểm tra khóa tài khoản
if ($_SESSION['login_attempts'] >= 5 && time() - $_SESSION['lockout_time'] < 300) {
    $error = "Tài khoản bị khóa tạm thời. Thử lại sau 5 phút.";
} else {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['code'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Yêu cầu không hợp lệ!";
        } else {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email không hợp lệ!";
            } else {
                $stmt = $conn->prepare("SELECT user_id, email, password, role FROM tbl_users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['role'] === 'user') {
                        $_SESSION['login_attempts'] = 0;
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        file_put_contents('debug.log', "Session sau khi đăng nhập: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
                        session_write_close();
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Tài khoản này không phải khách hàng!";
                    }
                } else {
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['lockout_time'] = time();
                        $error = "Đăng nhập thất bại quá nhiều lần. Tài khoản bị khóa 5 phút!";
                    } else {
                        $error = "Email hoặc mật khẩu không đúng!";
                    }
                }
                $stmt->close();
            }
        }
    }
}

// Thiết lập Google Client
require_once 'vendor/autoload.php';
$client = new Google_Client();
$client->setClientId('1060424966766-m04b1fpn35hba100rns0l05r50vc5tfk.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/phone-shop/callback.php');
$client->addScope('email');
$client->addScope('profile');
$googleLoginUrl = $client->createAuthUrl();

// Xử lý callback từ Google
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;

        $stmt = $conn->prepare("SELECT user_id, email, role FROM tbl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if ($user['role'] === 'user') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Tài khoản này không phải khách hàng!";
            }
        } else {
            $default_password = password_hash('default_password', PASSWORD_DEFAULT);
            $role = 'user';
            $insert_stmt = $conn->prepare("INSERT INTO tbl_users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $full_name = $google_account_info->name ?: 'Google User';
            $phone = '';
            $insert_stmt->bind_param("sssss", $full_name, $email, $default_password, $phone, $role);
            $insert_stmt->execute();
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    } else {
        $error = "Lỗi xác thực Google: " . $token['error_description'];
    }
}

$conn->close();
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
    <title>Đăng nhập Khách hàng</title>
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
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng nhập Khách hàng</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off" onsubmit="return validateForm()" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focius:ring-gray-500 focus:border-gray-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword()">
                        <i class="fas fa-eye text-gray-500" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg hover:bg-gray-900 transition">Đăng nhập</button>
        </form>
        <a href="<?php echo $googleLoginUrl; ?>" class="mt-4 w-full bg-white text-gray-700 border border-gray-300 py-2 rounded-lg hover:bg-gray-50 transition flex items-center justify-center">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="w-5 mr-2">
            Đăng nhập với Google
        </a>
        <div class="text-center mt-4 space-y-2">
            <p class="text-sm text-gray-600">Chưa có tài khoản? <a href="register_customer.php" class="text-blue-600 hover:underline">Đăng ký ngay</a></p>
            <p class="text-sm text-gray-600">Quên mật khẩu? <a href="forgot_password.php" class="text-blue-600 hover:underline">Khôi phục mật khẩu</a></p>
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
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                alert('Vui lòng nhập email hợp lệ!');
                return false;
            }
            if (password.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>