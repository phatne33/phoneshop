<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login_customer.php");
    exit();
}

// Kết nối database
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Cấu hình PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOtpEmail($to, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kanekikentp@gmail.com';
        $mail->Password = 'zfrmwfnkxuyctgvm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('kanekikentp@gmail.com', 'PhoneDB System');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Mã OTP Đổi Mật Khẩu - PhoneDB';
        $mail->Body = "Mã OTP của bạn là <b>$otp</b>. Mã này có hiệu lực trong 5 phút.";
        $mail->AltBody = "Mã OTP của bạn là $otp. Mã này có hiệu lực trong 5 phút.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}

$user_id = $_SESSION['user_id'];
$message = '';

// Lấy thông tin người dùng
$sql = "SELECT full_name, email, phone FROM tbl_users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE (email = ? OR phone = ?) AND user_id != ?");
    $stmt->bind_param("ssi", $email, $phone, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Email hoặc số điện thoại đã được sử dụng!";
    } else {
        $sql = "UPDATE tbl_users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);

        if ($stmt->execute()) {
            $message = "Cập nhật thông tin thành công!";
            $user = ['full_name' => $full_name, 'email' => $email, 'phone' => $phone];
        } else {
            $message = "Lỗi: " . $conn->error;
        }
    }
}

// Xử lý gửi OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999);
    
    // Lưu OTP vào database
    $sql = "INSERT INTO user_otp (user_id, otp, created_at) VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE otp = ?, created_at = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $otp, $otp);
    $stmt->execute();

    $email_result = sendOtpEmail($user['email'], $otp);

    if ($email_result === true) {
        $message = "OTP đã được gửi đến email của bạn!";
        $_SESSION['otp_sent'] = true;
    } else {
        $message = $email_result;
    }
}

// Xử lý xác thực OTP qua AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp']) && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if (!isset($_POST['otp']) || empty($_POST['otp'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập OTP.']);
        exit();
    }

    $input_otp = trim($_POST['otp']);
    $response = [];

    $sql = "SELECT otp, created_at FROM user_otp WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $otp_data = $result->fetch_assoc();
        $otp_time = strtotime($otp_data['created_at']);
        
        if (time() - $otp_time > 300) { // OTP hết hạn sau 5 phút
            $response = ['success' => false, 'message' => 'OTP đã hết hạn.'];
            $sql = "DELETE FROM user_otp WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        } elseif ($input_otp === $otp_data['otp']) {
            $_SESSION['otp_verified'] = true;
            $response = ['success' => true, 'message' => 'Xác thực OTP thành công!'];
            $sql = "DELETE FROM user_otp WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        } else {
            $response = ['success' => false, 'message' => 'OTP không đúng.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Không tìm thấy OTP. Vui lòng yêu cầu OTP mới.'];
    }

    echo json_encode($response);
    exit();
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        $message = "Vui lòng xác thực OTP trước.";
    } else {
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($new_password) || empty($confirm_password)) {
            $message = "Vui lòng nhập đầy đủ mật khẩu.";
        } elseif ($new_password !== $confirm_password) {
            $message = "Mật khẩu không khớp.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE tbl_users SET password = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $user_id);

            if ($stmt->execute()) {
                $message = "Đổi mật khẩu thành công!";
                unset($_SESSION['otp_verified'], $_SESSION['otp_sent']);
            } else {
                $message = "Lỗi: " . $conn->error;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .toast.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4 form-container">
            <h1 class="text-center mb-4">Thông Tin Tài Khoản</h1>

            <!-- Thông báo -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo strpos($message, 'thành công') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form chỉnh sửa thông tin -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" required>
                </div>
                <button type="submit" name="update_info" class="btn btn-primary w-100">Cập nhật</button>
            </form>

            <!-- Nút yêu cầu OTP -->
            <form method="POST" class="mb-4">
                <button type="submit" name="send_otp" class="btn btn-secondary w-100">Gửi OTP để đổi mật khẩu</button>
            </form>

            <!-- Form xác thực OTP -->
            <form id="otp-form" method="POST" class="mb-4 <?php echo isset($_SESSION['otp_sent']) ? '' : 'd-none'; ?>">
                <div class="mb-3">
                    <label for="otp" class="form-label">Mã OTP</label>
                    <input type="text" id="otp" name="otp" class="form-control" required>
                </div>
                <button type="button" onclick="verifyOtp()" class="btn btn-primary w-100">Xác thực OTP</button>
            </form>

            <!-- Form đổi mật khẩu -->
            <form id="password-form" method="POST" class="mb-4 <?php echo isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] ? '' : 'd-none'; ?>">
                <div class="mb-3">
                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary w-100">Đổi mật khẩu</button>
            </form>
        </div>

        <!-- Toast container -->
        <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, isSuccess) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${isSuccess ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message || 'Có lỗi xảy ra'}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            toastContainer.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        function verifyOtp() {
            const otp = document.getElementById('otp').value.trim();
            if (!otp) {
                showToast('Vui lòng nhập OTP.', false);
                return;
            }

            $.ajax({
                url: 'account.php',
                type: 'POST',
                data: { verify_otp: true, otp: otp, ajax: true },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX response:', response);
                    showToast(response.message, response.success);
                    if (response.success) {
                        document.getElementById('otp-form').classList.add('d-none');
                        document.getElementById('password-form').classList.remove('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    showToast('Đã xảy ra lỗi khi xác thực OTP: ' + (xhr.responseText || 'Không rõ'), false);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['otp_sent'])): ?>
                document.getElementById('otp-form').classList.remove('d-none');
            <?php endif; ?>
            <?php if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified']): ?>
                document.getElementById('otp-form').classList.add('d-none');
                document.getElementById('password-form').classList.remove('d-none');
            <?php endif; ?>
        });
    </script>
</body>
</html>