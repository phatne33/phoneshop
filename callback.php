<?php
define('ACCESS', true);
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Ghi log lỗi
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

require_once 'vendor/autoload.php';
$client = new Google_Client();
$client->setClientId('1060424966766-m04b1fpn35hba100rns0l05r50vc5tfk.apps.googleusercontent.com'); // Thêm Client ID của bạn
$client->setClientSecret('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'); // Thêm Client Secret của bạn
$client->setRedirectUri('http://localhost/phone-shop/callback.php');
$client->addScope('email');
$client->addScope('profile');

// Xử lý callback
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;

        // Kiểm tra hoặc tạo người dùng trong tbl_users
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
                header("Location: login_customer.php?error=" . urlencode($error));
                exit();
            }
        } else {
            // Tạo tài khoản mới nếu không tồn tại
            $default_password = password_hash('default_password', PASSWORD_DEFAULT);
            $role = 'user';
            $full_name = $google_account_info->name ?: 'Google User';
            $phone = '';
            $insert_stmt = $conn->prepare("INSERT INTO tbl_users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $full_name, $email, $default_password, $phone, $role);
            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                header("Location: index.php");
                exit();
            } else {
                $error = "Lỗi khi tạo tài khoản mới!";
                header("Location: login_customer.php?error=" . urlencode($error));
                exit();
            }
            $insert_stmt->close();
        }
        $stmt->close();
    } else {
        $error = "Lỗi xác thực Google: " . $token['error_description'];
        header("Location: login_customer.php?error=" . urlencode($error));
        exit();
    }
} else {
    header("Location: login_customer.php");
    exit();
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
    <title>Callback</title>
</head>
<body>
    <!-- Không cần nội dung HTML, vì callback tự động chuyển hướng -->
</body>
</html>