<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra vai trò admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Biến để lưu dữ liệu chỉnh sửa
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_sql = "SELECT * FROM tbl_promotions WHERE promo_id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Xử lý thêm hoặc sửa mã giảm giá
if (isset($_POST['save_promo'])) {
    $promo_code = trim($_POST['promo_code']);
    $discount_value = floatval($_POST['discount_value']);
    $discount_type = $_POST['discount_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
        // Cập nhật mã giảm giá
        $id = intval($_POST['promo_id']);
        $sql = "UPDATE tbl_promotions SET promo_code=?, discount_value=?, discount_type=?, start_date=?, end_date=?, status=? WHERE promo_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssi", $promo_code, $discount_value, $discount_type, $start_date, $end_date, $status, $id);
    } else {
        // Thêm mã giảm giá mới
        $sql = "INSERT INTO tbl_promotions (promo_code, discount_value, discount_type, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssss", $promo_code, $discount_value, $discount_type, $start_date, $end_date, $status);
    }
    $stmt->execute();
    header("Location: quanlypromotions.php?success=" . ($edit_data ? 'updated' : 'added'));
    exit();
}

// Xử lý xóa mã giảm giá
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM tbl_promotions WHERE promo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: quanlypromotions.php?success=deleted");
    exit();
}

// Lấy danh sách mã giảm giá
$sql = "SELECT * FROM tbl_promotions ORDER BY promo_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mã Giảm Giá</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
     
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 600;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        button[type="submit"] {
            background: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button[type="submit"]:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .action-btn {
            padding: 8px 15px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            transition: opacity 0.3s;
            display: inline-block;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .edit-btn {
            background: #ff9800;
        }
        .delete-btn {
            background: #dc3545;
        }
        @media (max-width: 768px) {
            .form-container, table {
                padding: 15px;
            }
            th, td {
                padding: 10px;
                font-size: 14px;
            }
            .action-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Mã Giảm Giá</h1>

        <!-- Form thêm/sửa mã giảm giá -->
        <div class="form-container">
            <h2 class="mb-4"><?= $edit_data ? 'Sửa Mã Giảm Giá' : 'Thêm Mã Giảm Giá' ?></h2>
            <form method="POST" action="">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="promo_id" value="<?= $edit_data['promo_id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="promo_code">Mã giảm giá</label>
                    <input type="text" name="promo_code" id="promo_code" value="<?= $edit_data ? htmlspecialchars($edit_data['promo_code']) : '' ?>" placeholder="Nhập mã giảm giá (ví dụ: SALE10)" required>
                </div>
                <div class="form-group">
                    <label for="discount_value">Giá trị giảm</label>
                    <input type="number" name="discount_value" id="discount_value" value="<?= $edit_data ? $edit_data['discount_value'] : '' ?>" step="0.01" min="0" placeholder="Nhập giá trị giảm" required>
                </div>
                <div class="form-group">
                    <label for="discount_type">Loại giảm giá</label>
                    <select name="discount_type" id="discount_type" required>
                        <option value="percentage" <?= $edit_data && $edit_data['discount_type'] === 'percentage' ? 'selected' : '' ?>>Phần trăm (%)</option>
                        <option value="fixed" <?= $edit_data && $edit_data['discount_type'] === 'fixed' ? 'selected' : '' ?>>Số tiền cố định (VNĐ)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu</label>
                    <input type="date" name="start_date" id="start_date" value="<?= $edit_data ? $edit_data['start_date'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Ngày hết hạn</label>
                    <input type="date" name="end_date" id="end_date" value="<?= $edit_data ? $edit_data['end_date'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" required>
                        <option value="active" <?= $edit_data && $edit_data['status'] === 'active' ? 'selected' : '' ?>>Kích hoạt</option>
                        <option value="inactive" <?= $edit_data && $edit_data['status'] === 'inactive' ? 'selected' : '' ?>>Không kích hoạt</option>
                    </select>
                </div>
                <button type="submit" name="save_promo"><?= $edit_data ? 'Cập nhật' : 'Thêm' ?></button>
            </form>
        </div>

        <!-- Danh sách mã giảm giá -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã</th>
                    <th>Giá trị giảm</th>
                    <th>Loại</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['promo_id'] ?></td>
                        <td><?= htmlspecialchars($row['promo_code']) ?></td>
                        <td><?= number_format($row['discount_value'], 2) ?> <?= $row['discount_type'] === 'percentage' ? '%' : 'VNĐ' ?></td>
                        <td><?= $row['discount_type'] === 'percentage' ? 'Phần trăm' : 'Cố định' ?></td>
                        <td><?= date('d/m/Y', strtotime($row['start_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['end_date'])) ?></td>
                        <td><?= $row['status'] === 'active' ? 'Kích hoạt' : 'Không kích hoạt' ?></td>
                        <td>
                            <a href="?edit=<?= $row['promo_id'] ?>" class="action-btn edit-btn">Sửa</a>
                            <a href="?delete=<?= $row['promo_id'] ?>" class="action-btn delete-btn" onclick="return confirmDelete(event)">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Hiển thị toast khi thêm/sửa/xóa thành công
        <?php if (isset($_GET['success'])): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?php 
                    if ($_GET['success'] == 'added') echo "Thêm mã giảm giá thành công!";
                    elseif ($_GET['success'] == 'updated') echo "Cập nhật mã giảm giá thành công!";
                    elseif ($_GET['success'] == 'deleted') echo "Xóa mã giảm giá thành công!";
                ?>',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Xác nhận xóa với SweetAlert
        function confirmDelete(event) {
            event.preventDefault();
            const url = event.target.href;
            Swal.fire({
                title: 'Bạn có chắc muốn xóa?',
                text: "Hành động này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>