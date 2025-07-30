<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    header("Location: login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Biến để lưu dữ liệu chỉnh sửa
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM tbl_news WHERE news_id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Xử lý thêm hoặc sửa bài viết
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = isset($_POST['current_image']) ? $_POST['current_image'] : '';

    // Xử lý upload hình ảnh mới (nếu có)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/news/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image_url = $image_name;
        } else {
            $message = "Lỗi khi upload hình ảnh.";
        }
    }

    if (empty($message)) {
        if (isset($_POST['news_id']) && !empty($_POST['news_id'])) {
            // Cập nhật bài viết
            $id = $_POST['news_id'];
            $sql = "UPDATE tbl_news SET title=?, content=?, image_url=? WHERE news_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $title, $content, $image_url, $id);
        } else {
            // Thêm bài viết mới
            $sql = "INSERT INTO tbl_news (title, content, image_url) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $title, $content, $image_url);
        }
        if ($stmt->execute()) {
            $message = isset($_POST['news_id']) ? "Cập nhật bài viết thành công!" : "Thêm bài viết thành công!";
            header("Location: add_news.php");
            exit();
        } else {
            $message = "Lỗi: " . $conn->error;
        }
    }
}

// Xử lý xóa bài viết
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM tbl_news WHERE news_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_news.php");
        exit();
    } else {
        $message = "Lỗi khi xóa bài viết: " . $conn->error;
    }
}

// Lấy danh sách bài viết
$sql = "SELECT * FROM tbl_news ORDER BY published_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Viết</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
     
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
       
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 200px;
            resize: vertical;
        }
        input[type="file"] {
            padding: 5px 0;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: <?php echo $message && strpos($message, 'thành công') !== false ? '#28a745' : '#dc3545'; ?>;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
        }
        .edit-btn {
            background: #ff9800;
        }
        .delete-btn {
            background: #f44336;
        }
        .news-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Bài Viết</h1>

        <!-- Form thêm/sửa bài viết -->
        <div class="form-container">
            <h2><?php echo $edit_data ? 'Sửa Bài Viết' : 'Thêm Bài Viết Mới'; ?></h2>
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="news_id" value="<?php echo $edit_data['news_id']; ?>">
                    <input type="hidden" name="current_image" value="<?php echo $edit_data['image_url']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="title">Tiêu đề</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" placeholder="Nhập tiêu đề bài viết" required>
                </div>
                <div class="form-group">
                    <label for="content">Nội dung</label>
                    <textarea id="content" name="content" placeholder="Nhập nội dung bài viết" required><?php echo $edit_data ? htmlspecialchars($edit_data['content']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Hình ảnh</label>
                    <?php if ($edit_data && $edit_data['image_url']): ?>
                        <img src="uploads/news/<?php echo htmlspecialchars($edit_data['image_url']); ?>" alt="Current Image" class="news-image">
                        <p>Chọn ảnh mới để thay thế (nếu muốn):</p>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <button type="submit" name="save_news"><?php echo $edit_data ? 'Cập nhật' : 'Thêm'; ?></button>
            </form>
        </div>

        <!-- Danh sách bài viết -->
        <h2>Danh Sách Bài Viết</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Hình ảnh</th>
                    <th>Ngày đăng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['news_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td>
                            <?php if ($row['image_url']): ?>
                                <img src="uploads/news/<?php echo htmlspecialchars($row['image_url']); ?>" alt="News Image" class="news-image">
                            <?php else: ?>
                                Không có ảnh
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['published_at'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $row['news_id']; ?>" class="action-btn edit-btn">Sửa</a>
                            <a href="?delete=<?php echo $row['news_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>