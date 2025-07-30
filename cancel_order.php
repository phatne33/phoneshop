<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$user_id || $user_id <= 0) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Yêu cầu không hợp lệ.";
    } else {
        $order_id = filter_var($_POST['cancel_order_id'], FILTER_VALIDATE_INT);
        if ($order_id === false || $order_id <= 0) {
            $error_message = "ID đơn hàng không hợp lệ.";
        } else {
            $sql_check = "SELECT status FROM tbl_orders WHERE order_id = ? AND user_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $order_id, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $order = $result_check->fetch_assoc();
                if ($order['status'] !== 'pending') {
                    $error_message = "Chỉ có thể hủy đơn hàng đang chờ xử lý.";
                } else {
                    $sql_cancel = "UPDATE tbl_orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ? AND status = 'pending'";
                    $stmt_cancel = $conn->prepare($sql_cancel);
                    $stmt_cancel->bind_param("ii", $order_id, $user_id);
                    if ($stmt_cancel->execute() && $stmt_cancel->affected_rows > 0) {
                        header("Location: index.php?page=myorders&success=1");
                        exit();
                    } else {
                        $error_message = "Không thể hủy đơn hàng. Vui lòng thử lại.";
                    }
                }
            } else {
                $error_message = "Đơn hàng không tồn tại hoặc không thuộc về bạn.";
            }
            $stmt_check->close();
            if (isset($stmt_cancel)) {
                $stmt_cancel->close();
            }
        }
    }
    // Nếu có lỗi, chuyển hướng với thông báo lỗi
    header("Location: index.php?page=myorders&error=" . urlencode($error_message));
    exit();
}
?>