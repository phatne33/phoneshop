<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Kết nối MySQLi
$conn = new mysqli('localhost', 'root', '', 'phonedb');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Lấy danh sách sản phẩm
$result = $conn->query("SELECT product_id, product_name FROM tbl_products WHERE status = 'active'");
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$result->free();

// Lấy danh sách Flash Sales
$result = $conn->query("SELECT * FROM tbl_flashsales ORDER BY start_time DESC");
$flashsales = [];
while ($row = $result->fetch_assoc()) {
    $flashsales[] = $row;
}
$result->free();

// Xử lý thêm Flash Sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = trim($_POST['title'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $products_selected = $_POST['products'] ?? [];
    $discount_values = $_POST['discount_value'] ?? [];
    $discount_types = $_POST['discount_type'] ?? [];
    $max_quantities = $_POST['max_quantity'] ?? [];

    // Validate đầu vào
    $errors = [];
    if (empty($title)) {
        $errors[] = "Vui lòng nhập tiêu đề cho Flash Sale.";
    }
    if (empty($start_time) || empty($end_time) || strtotime($start_time) >= strtotime($end_time)) {
        $errors[] = "Thời gian bắt đầu phải trước thời gian kết thúc.";
    }
    if (empty($products_selected)) {
        $errors[] = "Vui lòng chọn ít nhất một sản phẩm.";
    }

    foreach ($products_selected as $index => $product_id) {
        if (empty($product_id)) {
            $errors[] = "Sản phẩm tại dòng " . ($index + 1) . " không được để trống.";
        }
        if (!isset($discount_values[$index]) || $discount_values[$index] <= 0 || ($discount_types[$index] === 'percentage' && $discount_values[$index] > 100)) {
            $errors[] = "Giá trị giảm tại dòng " . ($index + 1) . " không hợp lệ.";
        }
        if (!isset($max_quantities[$index]) || $max_quantities[$index] <= 0) {
            $errors[] = "Số lượng tối đa tại dòng " . ($index + 1) . " phải lớn hơn 0.";
        }
    }

    if (!empty($errors)) {
        echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: '" . addslashes(implode("\\n", $errors)) . "', timer: 3000, showConfirmButton: false});</script>";
    } else {
        $conn->begin_transaction();
        try {
            // Thêm Flash Sale
            $stmt = $conn->prepare("INSERT INTO tbl_flashsales (title, start_time, end_time, status) VALUES (?, ?, ?, 'active')");
            $stmt->bind_param("sss", $title, $start_time, $end_time);
            $stmt->execute();
            $flashsale_id = $conn->insert_id;
            $stmt->close();

            // Cập nhật giá giảm
            foreach ($products_selected as $index => $product_id) {
                $discount_value = floatval($discount_values[$index]);
                $discount_type = $discount_types[$index];
                $max_quantity = intval($max_quantities[$index]);

                // Lấy variants
                $stmt = $conn->prepare("SELECT variant_id, price, original_price FROM tbl_product_variants WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $variants = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                foreach ($variants as $variant) {
                    $original_price = $variant['original_price'] ?? $variant['price'];
                    $new_price = $discount_type === 'percentage'
                        ? $original_price * (1 - $discount_value / 100)
                        : $original_price - $discount_value;

                    if ($new_price < 0) {
                        throw new Exception("Giá giảm không được âm cho sản phẩm ID $product_id.");
                    }

                    // Cập nhật giá
                    $stmt = $conn->prepare("UPDATE tbl_product_variants SET price = ?, original_price = ? WHERE variant_id = ?");
                    $stmt->bind_param("ddi", $new_price, $original_price, $variant['variant_id']);
                    $stmt->execute();
                    $stmt->close();
                }

                // Thêm vào tbl_flashsale
                $stmt = $conn->prepare("INSERT INTO tbl_flashsale (flashsale_id, product_id, sold_quantity, discount_value, discount_type, max_quantity) VALUES (?, ?, 0, ?, ?, ?)");
                $stmt->bind_param("iidsi", $flashsale_id, $product_id, $discount_value, $discount_type, $max_quantity);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            echo "<script>Swal.fire({icon: 'success', title: 'Thành công', text: 'Thêm Flash Sale thành công!', timer: 1500, showConfirmButton: false}).then(() => {window.location.href = 'index.php?page=flashsale&success=1';});</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: 'Lỗi thêm Flash Sale: " . addslashes($e->getMessage()) . "', timer: 2000, showConfirmButton: false});</script>";
        }
    }
}

// Xử lý sửa Flash Sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $flashsale_id = intval($_POST['flashsale_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $products_selected = $_POST['products'] ?? [];
    $discount_values = $_POST['discount_value'] ?? [];
    $discount_types = $_POST['discount_type'] ?? [];
    $max_quantities = $_POST['max_quantity'] ?? [];

    // Validate đầu vào
    $errors = [];
    if (empty($title)) {
        $errors[] = "Vui lòng nhập tiêu đề cho Flash Sale.";
    }
    if (empty($start_time) || empty($end_time) || strtotime($start_time) >= strtotime($end_time)) {
        $errors[] = "Thời gian bắt đầu phải trước thời gian kết thúc.";
    }
    if (empty($products_selected)) {
        $errors[] = "Vui lòng chọn ít nhất một sản phẩm.";
    }

    foreach ($products_selected as $index => $product_id) {
        if (empty($product_id)) {
            $errors[] = "Sản phẩm tại dòng " . ($index + 1) . " không được để trống.";
        }
        if (!isset($discount_values[$index]) || $discount_values[$index] <= 0 || ($discount_types[$index] === 'percentage' && $discount_values[$index] > 100)) {
            $errors[] = "Giá trị giảm tại dòng " . ($index + 1) . " không hợp lệ.";
        }
        if (!isset($max_quantities[$index]) || $max_quantities[$index] <= 0) {
            $errors[] = "Số lượng tối đa tại dòng " . ($index + 1) . " phải lớn hơn 0.";
        }
    }

    if (!empty($errors)) {
        echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: '" . addslashes(implode("\\n", $errors)) . "', timer: 3000, showConfirmButton: false});</script>";
    } else {
        $conn->begin_transaction();
        try {
            // Cập nhật thời gian và tiêu đề Flash Sale
            $stmt = $conn->prepare("UPDATE tbl_flashsales SET title = ?, start_time = ?, end_time = ? WHERE flashsale_id = ?");
            $stmt->bind_param("sssi", $title, $start_time, $end_time, $flashsale_id);
            $stmt->execute();
            $stmt->close();

            // Khôi phục giá gốc cho các sản phẩm cũ
            $stmt = $conn->prepare("SELECT product_id, sold_quantity, max_quantity FROM tbl_flashsale WHERE flashsale_id = ?");
            $stmt->bind_param("i", $flashsale_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if ($row['sold_quantity'] > $row['max_quantity']) {
                    throw new Exception("Số lượng đã bán vượt quá số lượng tối đa cho sản phẩm ID {$row['product_id']}.");
                }
                $stmt_restore = $conn->prepare("UPDATE tbl_product_variants SET price = original_price WHERE product_id = ?");
                $stmt_restore->bind_param("i", $row['product_id']);
                $stmt_restore->execute();
                $stmt_restore->close();
            }
            $stmt->close();

            // Xóa sản phẩm cũ
            $stmt = $conn->prepare("DELETE FROM tbl_flashsale WHERE flashsale_id = ?");
            $stmt->bind_param("i", $flashsale_id);
            $stmt->execute();
            $stmt->close();

            // Thêm sản phẩm mới và cập nhật giá
            foreach ($products_selected as $index => $product_id) {
                $discount_value = floatval($discount_values[$index]);
                $discount_type = $discount_types[$index];
                $max_quantity = intval($max_quantities[$index]);

                // Lấy variants
                $stmt = $conn->prepare("SELECT variant_id, price, original_price FROM tbl_product_variants WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $variants = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                foreach ($variants as $variant) {
                    $original_price = $variant['original_price'] ?? $variant['price'];
                    $new_price = $discount_type === 'percentage'
                        ? $original_price * (1 - $discount_value / 100)
                        : $original_price - $discount_value;

                    if ($new_price < 0) {
                        throw new Exception("Giá giảm không được âm cho sản phẩm ID $product_id.");
                    }

                    $stmt = $conn->prepare("UPDATE tbl_product_variants SET price = ?, original_price = ? WHERE variant_id = ?");
                    $stmt->bind_param("ddi", $new_price, $original_price, $variant['variant_id']);
                    $stmt->execute();
                    $stmt->close();
                }

                // Thêm vào tbl_flashsale
                $stmt = $conn->prepare("INSERT INTO tbl_flashsale (flashsale_id, product_id, sold_quantity, discount_value, discount_type, max_quantity) VALUES (?, ?, 0, ?, ?, ?)");
                $stmt->bind_param("iidsi", $flashsale_id, $product_id, $discount_value, $discount_type, $max_quantity);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            echo "<script>Swal.fire({icon: 'success', title: 'Thành công', text: 'Cập nhật Flash Sale thành công!', timer: 1500, showConfirmButton: false}).then(() => {window.location.href = 'index.php?page=flashsale&success=1';});</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: 'Lỗi cập nhật Flash Sale: " . addslashes($e->getMessage()) . "', timer: 2000, showConfirmButton: false});</script>";
        }
    }
}

// Xử lý xóa Flash Sale
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $flashsale_id = intval($_GET['id']);
    $conn->begin_transaction();
    try {
        // Khôi phục giá gốc
        $stmt = $conn->prepare("SELECT product_id FROM tbl_flashsale WHERE flashsale_id = ?");
        $stmt->bind_param("i", $flashsale_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stmt_restore = $conn->prepare("UPDATE tbl_product_variants SET price = original_price WHERE product_id = ?");
            $stmt_restore->bind_param("i", $row['product_id']);
            $stmt_restore->execute();
            $stmt_restore->close();
        }
        $stmt->close();

        // Xóa Flash Sale
        $stmt = $conn->prepare("DELETE FROM tbl_flashsale WHERE flashsale_id = ?");
        $stmt->bind_param("i", $flashsale_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM tbl_flashsales WHERE flashsale_id = ?");
        $stmt->bind_param("i", $flashsale_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "<script>Swal.fire({icon: 'success', title: 'Thành công', text: 'Xóa Flash Sale thành công!', timer: 1500, showConfirmButton: false}).then(() => {window.location.href = 'index.php?page=flashsale&success=1';});</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>Swal.fire({icon: 'error', title: 'Lỗi', text: 'Lỗi xóa Flash Sale: " . addslashes($e->getMessage()) . "', timer: 2000, showConfirmButton: false});</script>";
    }
}

// Lấy chi tiết Flash Sale nếu xem
$flashsale_details = null;
$flashsale_products = [];
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $flashsale_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_flashsales WHERE flashsale_id = ?");
    $stmt->bind_param("i", $flashsale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flashsale_details = $result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT fs.*, p.product_name FROM tbl_flashsale fs JOIN tbl_products p ON fs.product_id = p.product_id WHERE fs.flashsale_id = ?");
    $stmt->bind_param("i", $flashsale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flashsale_products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Lấy chi tiết Flash Sale nếu sửa
$edit_flashsale = null;
$edit_products = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $flashsale_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_flashsales WHERE flashsale_id = ?");
    $stmt->bind_param("i", $flashsale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_flashsale = $result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT fs.*, p.product_name FROM tbl_flashsale fs JOIN tbl_products p ON fs.product_id = p.product_id WHERE fs.flashsale_id = ?");
    $stmt->bind_param("i", $flashsale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Flash Sale</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .product-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .form-control { font-size: 14px; }
        .btn-sm { font-size: 12px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Quản lý Flash Sale</h2>

        <!-- Danh sách Flash Sales -->
        <h3>Danh sách Flash Sale</h3>
        <table class="table table-bordered" id="flashsale-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Thời gian bắt đầu</th>
                    <th>Thời gian kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flashsales as $flashsale): ?>
                    <tr>
                        <td><?php echo $flashsale['flashsale_id']; ?></td>
                        <td><?php echo htmlspecialchars($flashsale['title'] ?? 'Chưa có tiêu đề'); ?></td>
                        <td><?php echo htmlspecialchars($flashsale['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($flashsale['end_time']); ?></td>
                        <td><?php echo htmlspecialchars($flashsale['status']); ?></td>
                        <td>
                            <a href="index.php?page=flashsale&action=edit&id=<?php echo $flashsale['flashsale_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="index.php?page=flashsale&action=delete&id=<?php echo $flashsale['flashsale_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                            <a href="index.php?page=flashsale&action=view&id=<?php echo $flashsale['flashsale_id']; ?>" class="btn btn-info btn-sm">Xem</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Form thêm Flash Sale -->
        <h3>Thêm Flash Sale</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group mb-3">
                <label>Tiêu đề Flash Sale</label>
                <input type="text" name="title" class="form-control" required placeholder="Nhập tiêu đề Flash Sale">
            </div>
            <div class="form-group mb-3">
                <label>Thời gian bắt đầu</label>
                <input type="datetime-local" name="start_time" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label>Thời gian kết thúc</label>
                <input type="datetime-local" name="end_time" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label>Sản phẩm tham gia</label>
                <div id="products">
                    <div class="product-row">
                        <select name="products[]" class="form-control" style="width: 40%;" required>
                            <option value="">Chọn sản phẩm</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="discount_value[]" class="form-control" style="width: 15%;" placeholder="Giá trị giảm" required min="0" step="0.01">
                        <select name="discount_type[]" class="form-control" style="width: 15%;" required>
                            <option value="percentage">Phần trăm</option>
                            <option value="fixed">Cố định</option>
                        </select>
                        <input type="number" name="max_quantity[]" class="form-control" style="width: 20%;" placeholder="Số lượng tối đa" required min="1" step="1">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)">Xóa</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mt-2" onclick="addProductRow()">Thêm sản phẩm</button>
            </div>
            <button type="submit" class="btn btn-primary">Lưu</button>
        </form>

        <!-- Form sửa Flash Sale -->
        <?php if ($edit_flashsale): ?>
            <h3>Sửa Flash Sale #<?php echo $edit_flashsale['flashsale_id']; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="flashsale_id" value="<?php echo $edit_flashsale['flashsale_id']; ?>">
                <div class="form-group mb-3">
                    <label>Tiêu đề Flash Sale</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_flashsale['title'] ?? ''); ?>" required placeholder="Nhập tiêu đề Flash Sale">
                </div>
                <div class="form-group mb-3">
                    <label>Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_time" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($edit_flashsale['start_time'])); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label>Thời gian kết thúc</label>
                    <input type="datetime-local" name="end_time" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($edit_flashsale['end_time'])); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label>Sản phẩm tham gia</label>
                    <div id="edit-products">
                        <?php foreach ($edit_products as $product): ?>
                            <div class="product-row">
                                <select name="products[]" class="form-control" style="width: 40%;" required>
                                    <option value="">Chọn sản phẩm</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['product_id']; ?>" <?php echo $p['product_id'] == $product['product_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['product_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="discount_value[]" class="form-control" style="width: 15%;" value="<?php echo $product['discount_value']; ?>" required min="0" step="0.01">
                                <select name="discount_type[]" class="form-control" style="width: 15%;" required>
                                    <option value="percentage" <?php echo $product['discount_type'] === 'percentage' ? 'selected' : ''; ?>>Phần trăm</option>
                                    <option value="fixed" <?php echo $product['discount_type'] === 'fixed' ? 'selected' : ''; ?>>Cố định</option>
                                </select>
                                <input type="number" name="max_quantity[]" class="form-control" style="width: 20%;" value="<?php echo $product['max_quantity']; ?>" required min="1" step="1">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)">Xóa</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" onclick="addProductRow('edit-products')">Thêm sản phẩm</button>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </form>
        <?php endif; ?>

        <!-- Chi tiết Flash Sale -->
        <?php if ($flashsale_details): ?>
            <h3>Chi tiết Flash Sale #<?php echo $flashsale_details['flashsale_id']; ?></h3>
            <p><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($flashsale_details['title'] ?? 'Chưa có tiêu đề'); ?></p>
            <p><strong>Thời gian bắt đầu:</strong> <?php echo htmlspecialchars($flashsale_details['start_time']); ?></p>
            <p><strong>Thời gian kết thúc:</strong> <?php echo htmlspecialchars($flashsale_details['end_time']); ?></p>
            <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($flashsale_details['status']); ?></p>
            <h4>Sản phẩm tham gia</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Giá trị giảm</th>
                        <th>Loại giảm</th>
                        <th>Số lượng đã bán</th>
                        <th>Số lượng tối đa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flashsale_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo $product['discount_value']; ?> <?php echo $product['discount_type'] === 'percentage' ? '%' : 'VNĐ'; ?></td>
                            <td><?php echo $product['discount_type'] === 'percentage' ? 'Phần trăm' : 'Cố định'; ?></td>
                            <td><?php echo $product['sold_quantity']; ?></td>
                            <td><?php echo $product['max_quantity']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function addProductRow(containerId = 'products') {
            const container = document.getElementById(containerId);
            const template = container.querySelector('.product-row').cloneNode(true);
            template.querySelectorAll('input, select').forEach(input => input.value = '');
            template.querySelector('button').style.display = 'inline-block';
            container.appendChild(template);
        }

        function removeProductRow(button) {
            const container = button.closest('.product-row').parentNode;
            if (container.querySelectorAll('.product-row').length > 1) {
                button.closest('.product-row').remove();
            }
        }

        // Gọi restore_flashsale.php mỗi 60 giây
        setInterval(function() {
            fetch('restore_flashsale.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Xử lý khôi phục giá
                if (data.restore.status === 'success' && data.restore.updated_count > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: `Đã khôi phục giá cho ${data.restore.updated_count} sản phẩm`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else if (data.restore.status === 'error') {
                    console.error('Lỗi khôi phục:', data.restore.message);
                }

                // Cập nhật danh sách Flash Sale
                if (data.flashsales.status === 'success') {
                    updateFlashSaleTable(data.flashsales.flashsales);
                } else {
                    console.error('Lỗi tải Flash Sales:', data.flashsales.message);
                }
            })
            .catch(error => console.error('Lỗi AJAX:', error));
        }, 60000);

        // Hàm cập nhật bảng Flash Sale
        function updateFlashSaleTable(flashsales) {
            const tbody = document.querySelector('#flashsale-table tbody');
            tbody.innerHTML = '';
            flashsales.forEach(flashsale => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${flashsale.flashsale_id}</td>
                    <td>${flashsale.title || 'Chưa có tiêu đề'}</td>
                    <td>${flashsale.start_time}</td>
                    <td>${flashsale.end_time}</td>
                    <td>${flashsale.status}</td>
                    <td>
                        <a href="index.php?page=flashsale&action=edit&id=${flashsale.flashsale_id}" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="index.php?page=flashsale&action=delete&id=${flashsale.flashsale_id}" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                        <a href="index.php?page=flashsale&action=view&id=${flashsale.flashsale_id}" class="btn btn-info btn-sm">Xem</a>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>