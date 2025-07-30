<?php
define('ACCESS', true);
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Set thành true nếu dùng HTTPS
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Cấu hình ghi log lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Khởi tạo biến
$error = '';
$success = '';
$step = isset($_SESSION['forgot_password_step']) ? $_SESSION['forgot_password_step'] : 'request_email';

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Yêu cầu không hợp lệ!";
    } else {
        // Bước 1: Yêu cầu gửi OTP qua email
        if ($step === 'request_email' && isset($_POST['email'])) {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email không hợp lệ!";
            } else {
                // Kiểm tra email có tồn tại trong bảng tbl_users không
                $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    // Xóa các OTP hết hạn (hơn 5 phút)
                    $stmt = $conn->prepare("DELETE FROM user_otp WHERE created_at < NOW() - INTERVAL 5 MINUTE");
                    $stmt->execute();

                    // Tạo và lưu OTP mới
                    $otp = sprintf("%06d", mt_rand(100000, 999999));
                    $stmt = $conn->prepare("INSERT INTO user_otp (user_id, otp, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE otp = ?, created_at = NOW()");
                    $stmt->bind_param("iss", $user['user_id'], $otp, $otp);
                    if ($stmt->execute()) {
                        // Gửi email OTP
                        $mail = new PHPMailer(true);
                        try {
                            $mail->SMTPDebug = 2;
                            $mail->Debugoutput = function($str, $level) {
                                file_put_contents('php_errors.log', "PHPMailer Debug [$level]: $str\n", FILE_APPEND);
                            };

                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'kanekikentp@gmail.com'; // Thay bằng email của bạn
                            $mail->Password = 'zfrmwfnkxuyctgvm'; // Thay bằng App Password của bạn
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;

                            $mail->setFrom('kanekikentp@gmail.com', 'Phone Shop');
                            $mail->addAddress($email);
                            $mail->CharSet = 'UTF-8';
                            $mail->isHTML(true);
                            $mail->Subject = 'Mã OTP để đặt lại mật khẩu';
                            $mail->Body = "
                                <div style='font-family: Arial, sans-serif;'>
                                    <h2 style='color: #333;'>Xin chào,</h2>
                                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                                    <p>Mã OTP của bạn là: <strong style='font-size: 18px; color: #000000;'>$otp</strong></p>
                                    <p>Mã này có hiệu lực trong 5 phút. Vui lòng không chia sẻ mã này với bất kỳ ai.</p>
                                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                                    <p>Trân trọng,<br>Phone Shop</p>
                                </div>
                            ";

                            if ($mail->send()) {
                                $_SESSION['forgot_password_email'] = $email;
                                $_SESSION['forgot_password_step'] = 'verify_otp';
                                $success = "Mã OTP đã được gửi đến email của bạn! Vui lòng kiểm tra hộp thư (hoặc thư rác).";
                            } else {
                                $error = "Không thể gửi OTP. Vui lòng thử lại sau!";
                                file_put_contents('php_errors.log', "PHPMailer Error: Failed to send email to $email\n", FILE_APPEND);
                            }
                        } catch (Exception $e) {
                            $error = "Không thể gửi OTP. Vui lòng thử lại sau!";
                            file_put_contents('php_errors.log', "PHPMailer Exception: {$mail->ErrorInfo}\n", FILE_APPEND);
                        }
                    } else {
                        $error = "Lỗi khi lưu OTP vào cơ sở dữ liệu!";
                        file_put_contents('php_errors.log', "Database Error: Failed to insert/update OTP for user_id {$user['user_id']}\n", FILE_APPEND);
                    }
                } else {
                    $error = "Email không tồn tại trong hệ thống!";
                }
                $stmt->close();
            }
        }
        // Bước 2: Xác minh OTP
        elseif ($step === 'verify_otp' && isset($_POST['otp'])) {
            $otp = trim($_POST['otp']);
            $email = $_SESSION['forgot_password_email'];

            // Kiểm tra OTP và thời gian hiệu lực
            $stmt = $conn->prepare("SELECT user_id, otp FROM user_otp WHERE user_id = (SELECT user_id FROM tbl_users WHERE email = ?) AND created_at >= NOW() - INTERVAL 5 MINUTE");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $otp_record = $result->fetch_assoc();

            if ($otp_record) {
                if ($otp_record['otp'] === $otp) {
                    $_SESSION['forgot_password_step'] = 'reset_password';
                    $success = "Xác minh OTP thành công! Vui lòng nhập mật khẩu mới.";
                    header("Refresh:0"); // Tự động làm mới trang để hiển thị form đặt lại mật khẩu
                } else {
                    $error = "Mã OTP không chính xác!";
                    file_put_contents('php_errors.log', "OTP Mismatch: Input OTP: $otp, Stored OTP: {$otp_record['otp']}\n", FILE_APPEND);
                }
            } else {
                $error = "Mã OTP đã hết hạn! Vui lòng yêu cầu mã mới.";
                file_put_contents('php_errors.log', "OTP Expired: No valid OTP record for email $email\n", FILE_APPEND);
            }
            $stmt->close();
        }
        // Bước 3: Đặt lại mật khẩu
        elseif ($step === 'reset_password' && isset($_POST['password']) && isset($_POST['confirm_password'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $email = $_SESSION['forgot_password_email'];

            if ($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp!";
            } elseif (strlen($password) < 6) {
                $error = "Mật khẩu phải có ít nhất 6 ký tự!";
            } else {
                // Kiểm tra email tồn tại
                $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    $error = "Email không tồn tại trong hệ thống!";
                } else {
                    // Mã hóa và cập nhật mật khẩu
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE tbl_users SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    if ($stmt->execute()) {
                        // Xóa OTP sau khi đặt lại mật khẩu
                        $stmt = $conn->prepare("DELETE FROM user_otp WHERE user_id = (SELECT user_id FROM tbl_users WHERE email = ?)");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        unset($_SESSION['forgot_password_email']);
                        unset($_SESSION['forgot_password_step']);
                        $success = "Đặt lại mật khẩu thành công! Vui lòng <a href='login_customer.php' class='text-blue-600 hover:underline'>đăng nhập</a>.";
                    } else {
                        $error = "Đã có lỗi xảy ra. Vui lòng thử lại!";
                        file_put_contents('php_errors.log', "Database Error: Failed to update password for email $email\n", FILE_APPEND);
                    }
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Khôi phục mật khẩu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        @media (min-width: 1536px) {
    .container {
        max-width: 500px;
    }
}
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4b5563;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #1f2937;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #111827;
        }
        .message-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .link-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #2563eb;
            text-decoration: none;
        }
        .link-back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="container" style="width: 500px;">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            <?php
            if ($step === 'request_email') {
                echo 'Khôi phục mật khẩu';
            } elseif ($step === 'verify_otp') {
                echo 'Xác minh OTP';
            } else {
                echo 'Đặt lại mật khẩu';
            }
            ?>
        </h2>
        <?php if ($error): ?>
            <div class="message-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <?php if ($step === 'request_email'): ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn-primary">Gửi OTP</button>
            <?php elseif ($step === 'verify_otp'): ?>
                <div class="form-group">
                    <label for="otp">Mã OTP</label>
                    <input type="text" id="otp" name="otp" required>
                </div>
                <button type="submit" class="btn-primary">Xác minh OTP</button>
            <?php else: ?>
                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('password', 'toggleIconPassword')">
                            <i class="fas fa-eye text-gray-500" id="toggleIconPassword"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword('confirm_password', 'toggleIconConfirm')">
                            <i class="fas fa-eye text-gray-500" id="toggleIconConfirm"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Đặt lại mật khẩu</button>
            <?php endif; ?>
        </form>
        <a href="login_customer.php" class="link-back">Quay lại Đăng nhập</a>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>