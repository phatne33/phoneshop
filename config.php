<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mật khẩu rỗng cho XAMPP mặc định
define('DB_NAME', 'phonedb');

// Bật báo lỗi MySQL trong môi trường phát triển
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    // Đặt charset để hỗ trợ tiếng Việt
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Ghi log lỗi thay vì hiển thị trực tiếp
    error_log("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Đóng kết nối khi cần (tùy chọn, thường không cần trong PHP vì kết nối tự đóng khi script kết thúc)
// register_shutdown_function(function() use ($conn) {
//     $conn->close();
// });
?>