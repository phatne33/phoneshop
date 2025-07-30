<?php
// restore_flashsale.php

// Bảo mật: Chỉ cho phép truy cập từ AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Truy cập không hợp lệ');
}

// Đặt múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối MySQLi
$conn = new mysqli('localhost', 'root', '', 'phonedb');
if ($conn->connect_error) {
    error_log("Kết nối thất bại: " . $conn->connect_error);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Kết nối database thất bại']);
    exit;
}
$conn->set_charset("utf8mb4");

// Hàm khôi phục giá gốc
function restoreOriginalPrices($conn) {
    $conn->begin_transaction();
    try {
        // Lấy danh sách Flash Sale hết hạn
        $result = $conn->query("SELECT flashsale_id FROM tbl_flashsales WHERE end_time <= NOW() AND status = 'active' LIMIT 10");
        $flashsales_expired = [];
        while ($row = $result->fetch_assoc()) {
            $flashsales_expired[] = $row['flashsale_id'];
        }

        $updated_count = 0;
        foreach ($flashsales_expired as $flashsale_id) {
            // Cập nhật trạng thái Flash Sale
            $stmt = $conn->prepare("UPDATE tbl_flashsales SET status = 'expired' WHERE flashsale_id = ?");
            $stmt->bind_param("i", $flashsale_id);
            $stmt->execute();
            $stmt->close();

            // Lấy danh sách sản phẩm trong Flash Sale
            $stmt = $conn->prepare("SELECT product_id FROM tbl_flashsale WHERE flashsale_id = ?");
            $stmt->bind_param("i", $flashsale_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row['product_id'];
            }
            $stmt->close();

            // Khôi phục giá gốc
            foreach ($products as $product_id) {
                $stmt = $conn->prepare("UPDATE tbl_product_variants SET price = original_price WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $updated_count += $stmt->affected_rows;
                $stmt->close();
            }
        }

        $conn->commit();
        return ['status' => 'success', 'updated_count' => $updated_count];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Lỗi khôi phục giá: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Lỗi khôi phục giá: ' . $e->getMessage()];
    }
}

// Xử lý yêu cầu
header('Content-Type: application/json');
$response = [];

// Khôi phục giá
$restore_result = restoreOriginalPrices($conn);
$response['restore'] = $restore_result;

// Lấy danh sách Flash Sales
$result = $conn->query("SELECT * FROM tbl_flashsales ORDER BY start_time DESC");
$flashsales = [];
while ($row = $result->fetch_assoc()) {
    $flashsales[] = $row;
}
$result->free();
$response['flashsales'] = ['status' => 'success', 'flashsales' => $flashsales];

// Trả về kết quả
echo json_encode($response);

// Đóng kết nối
$conn->close();
?>