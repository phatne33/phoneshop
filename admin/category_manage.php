<?php
// Bắt đầu session nếu chưa có (đã xử lý trong index.php, nhưng giữ để an toàn)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin (đã xử lý trong index.php, nhưng giữ để độc lập)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Kết nối cơ sở dữ liệu (đã xử lý trong index.php, nhưng giữ để độc lập)
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Biến khởi tạo
$category_name = "";
$description = "";
$category_id = "";
$category_logo = "";

// Lấy dữ liệu danh mục cần sửa
if (isset($_GET['edit'])) {
    $category_id = intval($_GET['edit']); // Chuyển sang số nguyên để tránh SQL injection
    $stmt = $conn->prepare("SELECT * FROM tbl_categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $category_name = $row['category_name'];
        $description = $row['description'];
        $category_logo = $row['category_logo'];
    }
    $stmt->close();
}

// Xử lý thêm/sửa danh mục
if (isset($_POST['save'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $logo = "";

    // Xử lý upload ảnh
    if (!empty($_FILES['category_logo']['name'])) {
        $target_dir = "uploads/";
        $logo = time() . "_" . basename($_FILES["category_logo"]["name"]);
        $target_file = $target_dir . $logo;
        move_uploaded_file($_FILES["category_logo"]["tmp_name"], $target_file);
    }

    if (!empty($_POST['category_id'])) {
        // Sửa danh mục
        $id = intval($_POST['category_id']);
        $stmt = $conn->prepare("UPDATE tbl_categories SET category_name = ?, description = ? WHERE category_id = ?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        if ($stmt->execute() && $logo) {
            $stmt = $conn->prepare("UPDATE tbl_categories SET category_logo = ? WHERE category_id = ?");
            $stmt->bind_param("si", $logo, $id);
            $stmt->execute();
        }
    } else {
        // Thêm danh mục mới
        $stmt = $conn->prepare("INSERT INTO tbl_categories (category_name, description, category_logo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $desc, $logo);
        $stmt->execute();
    }
    $stmt->close();
    echo "<script>window.location.href='index.php?page=categories';</script>";
    exit();
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']); // Chuyển sang số nguyên

    // Lấy tên file ảnh để xóa
    $stmt = $conn->prepare("SELECT category_logo FROM tbl_categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $logo_path = "uploads/" . $row['category_logo'];
        if (file_exists($logo_path)) {
            unlink($logo_path); // Xóa ảnh khỏi thư mục uploads
        }
    }
    $stmt->close();

    // Xóa danh mục khỏi database
    $stmt = $conn->prepare("DELETE FROM tbl_categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>window.location.href='index.php?page=categories';</script>";
    exit();
}

// Lấy danh sách danh mục để hiển thị
$categories = [];
$sql = "SELECT * FROM tbl_categories";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
$result->free();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh Mục</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="content">
        <div class="form-container">
            <h2 class="text-center"><?php echo isset($_GET['edit']) ? 'Sửa Danh Mục' : 'Thêm Danh Mục'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
                <div class="mb-3">
                    <label class="form-label">Tên Danh Mục</label>
                    <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category_name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô Tả</label>
                    <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($description) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo Danh Mục</label>
                    <input type="file" name="category_logo" class="form-control">
                    <?php if (!empty($category_logo)) : ?>
                        <img src="uploads/<?= htmlspecialchars($category_logo) ?>" alt="Logo" width="100" class="mt-2">
                    <?php endif; ?>
                </div>
                <button type="submit" name="save" class="btn btn-primary">Lưu</button>
            </form>
        </div>

        <div class="table-container mt-4">
            <h2 class="text-center">Quản lý Danh Mục</h2>
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Tên Danh Mục</th>
                        <th>Mô Tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $row): ?>
                    <?php
                    // Xóa số đứng trước tên file ảnh (nếu có)
                    $category_logo = preg_replace('/^\d+_/', '', $row['category_logo']);
                    ?>
                    <tr>
                        <td><?= $row['category_id'] ?></td>
                        <td>
                            <?php if (!empty($category_logo)): ?>
                                <img src="http://localhost/phone-shop/public/images/<?= htmlspecialchars($category_logo) ?>" width="150" height="50">
                            <?php else: ?>
                                Không có ảnh
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <a href="index.php?page=categories&edit=<?= $row['category_id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="index.php?page=categories&delete=<?= $row['category_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa không?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>