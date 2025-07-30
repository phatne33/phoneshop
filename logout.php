<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy(); // Hủy toàn bộ session
header("Location: login_customer.php"); // Chuyển hướng về trang đăng nhập
exit();
?>