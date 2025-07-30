<?php
// Bắt đầu session nếu chưa có (đã xử lý trong index.php, nhưng giữ để an toàn)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    header("Location: login.php");
    exit();
}

// Xử lý phản hồi admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_review'])) {
    $review_id = $_POST['review_id'];
    $admin_reply = trim($_POST['admin_reply']); // Loại bỏ khoảng trắng thừa
    $stmt = $conn->prepare("UPDATE tbl_reviews SET admin_reply = ? WHERE review_id = ?");
    $stmt->bind_param("si", $admin_reply, $review_id);
    if ($stmt->execute()) {
        // Thành công, chuyển hướng
        header("Location: http://localhost/phone-shop/admin/index.php?page=reviews");
        exit();
    } else {
        // In lỗi nếu cập nhật thất bại
        die("Lỗi cập nhật admin_reply: " . $stmt->error);
    }
    $stmt->close();
}

// Xử lý xóa bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    $stmt = $conn->prepare("DELETE FROM tbl_reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    if ($stmt->execute()) {
        header("Location: admin_reviews.php");
        exit();
    } else {
        die("Lỗi xóa bình luận: " . $stmt->error);
    }
    $stmt->close();
}

// Truy vấn danh sách bình luận
$sql = "SELECT r.review_id, r.user_id, u.full_name, r.product_id, p.product_name, r.rating, r.comment, r.created_at, r.admin_reply 
        FROM tbl_reviews r
        JOIN tbl_users u ON r.user_id = u.user_id
        JOIN tbl_products p ON r.product_id = p.product_id
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);

// Kiểm tra lỗi truy vấn
if ($result === false) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bình luận</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Quản lý Bình luận</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 p-2">ID</th>
                        <th class="border border-gray-300 p-2">Người dùng</th>
                        <th class="border border-gray-300 p-2">Sản phẩm</th>
                        <th class="border border-gray-300 p-2">Rating</th>
                        <th class="border border-gray-300 p-2">Bình luận</th>
                        <th class="border border-gray-300 p-2">Ngày tạo</th>
                        <th class="border border-gray-300 p-2">Phản hồi</th>
                        <th class="border border-gray-300 p-2">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="bg-white border-b border-gray-300">
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['review_id']) ?></td>
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['product_name']) ?></td>
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['rating']) ?>/5</td>
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['comment']) ?></td>
                            <td class="p-2 border border-gray-300"><?= htmlspecialchars($row['created_at']) ?></td>
                            <td class="p-2 border border-gray-300">
                                <form action="index.php?page=reviews" method="post" class="flex flex-col">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <textarea name="admin_reply" class="border rounded p-2 w-full" placeholder="Nhập phản hồi..."><?= htmlspecialchars($row['admin_reply'] ?? '') ?></textarea>
                                    <button type="submit" name="reply_review" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Trả lời</button>
                                </form>
                            </td>
                            <td class="p-2 border border-gray-300">
                                <form action="index.php?page=reviews" method="post">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <button type="submit" name="delete_review" onclick="return confirm('Xóa bình luận này?');" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-2 text-center">Không có bình luận nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>