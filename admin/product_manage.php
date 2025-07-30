<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
$role = $_SESSION['role'] ?? null;
if (!$user_id || $role !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$conn->set_charset("utf8mb4");

$product_name = $category_id = $product_description = $quantity = "";
$product_specs = [];
$product_variants = [];
$product_colors = [];
$product_images = [];
$product_id = filter_var($_GET['edit'] ?? '', FILTER_VALIDATE_INT) ?: "";

// Lấy dữ liệu sản phẩm cần sửa
if ($product_id) {
    $query = "SELECT * FROM tbl_products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $product_name = $row['product_name'];
        $category_id = $row['category_id'];
        $product_description = $row['description'];
        $quantity = $row['quantity'];

        // Lấy thông số cố định từ tbl_product_specifications
        $spec_query = "SELECT * FROM tbl_product_specifications WHERE product_id = ? LIMIT 1";
        $spec_stmt = $conn->prepare($spec_query);
        $spec_stmt->bind_param("i", $product_id);
        $spec_stmt->execute();
        $spec_result = $spec_stmt->get_result();
        if ($spec = $spec_result->fetch_assoc()) {
            $product_specs = $spec;
        }

        // Lấy biến thể từ tbl_product_variants
        $variant_query = "SELECT variant_id, ram, storage, price FROM tbl_product_variants WHERE product_id = ?";
        $variant_stmt = $conn->prepare($variant_query);
        $variant_stmt->bind_param("i", $product_id);
        $variant_stmt->execute();
        $variant_result = $variant_stmt->get_result();
        while ($variant = $variant_result->fetch_assoc()) {
            $product_variants[] = $variant;
        }

        // Lấy màu sắc từ tbl_product_colors
        $color_query = "SELECT * FROM tbl_product_colors WHERE product_id = ?";
        $color_stmt = $conn->prepare($color_query);
        $color_stmt->bind_param("i", $product_id);
        $color_stmt->execute();
        $color_result = $color_stmt->get_result();
        while ($color = $color_result->fetch_assoc()) {
            $product_colors[$color['color_id']] = $color['color_name'];
        }

        // Lấy ảnh từ tbl_product_images
        $image_query = "SELECT image_id, color_id, image_url FROM tbl_product_images WHERE product_id = ?";
        $image_stmt = $conn->prepare($image_query);
        $image_stmt->bind_param("i", $product_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result();
        while ($img = $image_result->fetch_assoc()) {
            $color_id = $img['color_id'] ?? 'no_color';
            $product_images[$color_id][] = $img;
        }
    }

    $stmt->close();
    if (isset($spec_stmt)) $spec_stmt->close();
    if (isset($variant_stmt)) $variant_stmt->close();
    if (isset($color_stmt)) $color_stmt->close();
    if (isset($image_stmt)) $image_stmt->close();
}

// Xử lý thêm/sửa sản phẩm
if (isset($_POST['save']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = intval($_POST['category_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = intval($_POST['quantity'] ?? 0);
    $screen_size = mysqli_real_escape_string($conn, $_POST['screen_size'] ?? '');
    $refresh_rate = mysqli_real_escape_string($conn, $_POST['refresh_rate'] ?? '');
    $os = mysqli_real_escape_string($conn, $_POST['os'] ?? '');
    $chipset = mysqli_real_escape_string($conn, $_POST['chipset'] ?? '');
    $rear_camera = mysqli_real_escape_string($conn, $_POST['rear_camera'] ?? '');
    $front_camera = mysqli_real_escape_string($conn, $_POST['front_camera'] ?? '');
    $battery = mysqli_real_escape_string($conn, $_POST['battery'] ?? '');

    if ($product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT)) {
        $update_query = "UPDATE tbl_products SET product_name=?, category_id=?, description=?, quantity=? WHERE product_id=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sisii", $name, $category, $description, $quantity, $product_id);
        $update_stmt->execute();
        $id = $product_id;

        // Xóa thông số và biến thể cũ trước khi thêm mới
        $conn->query("DELETE FROM tbl_product_specifications WHERE product_id = $id");
        $conn->query("DELETE FROM tbl_product_variants WHERE product_id = $id");
    } else {
        $insert_query = "INSERT INTO tbl_products (product_name, category_id, description, quantity, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'active')";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sisi", $name, $category, $description, $quantity);
        $insert_stmt->execute();
        $id = $conn->insert_id;
    }

    // Lưu thông số cố định vào tbl_product_specifications
    if (!empty($screen_size) || !empty($refresh_rate) || !empty($os) || !empty($chipset) || !empty($rear_camera) || !empty($front_camera) || !empty($battery)) {
        $insert_spec_query = "INSERT INTO tbl_product_specifications (product_id, screen_size, refresh_rate, os, chipset, rear_camera, front_camera, battery) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $spec_stmt = $conn->prepare($insert_spec_query);
        $spec_stmt->bind_param("isssssss", $id, $screen_size, $refresh_rate, $os, $chipset, $rear_camera, $front_camera, $battery);
        $spec_stmt->execute();
    }

    // Lưu biến thể vào tbl_product_variants
    if (isset($_POST['rams']) && isset($_POST['storages']) && isset($_POST['prices'])) {
        foreach ($_POST['rams'] as $index => $ram) {
            if (!empty($ram) && !empty($_POST['storages'][$index]) && !empty($_POST['prices'][$index])) {
                $ram = mysqli_real_escape_string($conn, $ram);
                $storage = mysqli_real_escape_string($conn, $_POST['storages'][$index]);
                $price = floatval($_POST['prices'][$index]);
                $insert_variant_query = "INSERT INTO tbl_product_variants (product_id, ram, storage, price) VALUES (?, ?, ?, ?)";
                $variant_stmt = $conn->prepare($insert_variant_query);
                $variant_stmt->bind_param("issd", $id, $ram, $storage, $price);
                $variant_stmt->execute();
            }
        }
    }

    // Xử lý màu sắc
    $existing_colors = [];
    $color_query = "SELECT color_id, color_name FROM tbl_product_colors WHERE product_id = ?";
    $color_stmt = $conn->prepare($color_query);
    $color_stmt->bind_param("i", $id);
    $color_stmt->execute();
    $color_result = $color_stmt->get_result();
    while ($color = $color_result->fetch_assoc()) {
        $existing_colors[$color['color_name']] = $color['color_id'];
    }

    if (isset($_POST['colors'])) {
        $new_colors = array_filter($_POST['colors']);
        $color_ids = [];
        foreach ($new_colors as $index => $color) {
            $color = mysqli_real_escape_string($conn, $color);
            $color_id = $existing_colors[$color] ?? null;

            if (!$color_id) {
                $insert_color_query = "INSERT INTO tbl_product_colors (product_id, color_name) VALUES (?, ?)";
                $insert_color_stmt = $conn->prepare($insert_color_query);
                $insert_color_stmt->bind_param("is", $id, $color);
                $insert_color_stmt->execute();
                $color_id = $conn->insert_id;
            }
            $color_ids[$index] = $color_id;

            // Xử lý ảnh
            $file_key = "images_" . $index;
            if (!empty($_FILES[$file_key]['name'][0])) {
                $conn->query("DELETE FROM tbl_product_images WHERE product_id = $id AND color_id = $color_id");
                foreach ($_FILES[$file_key]['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES[$file_key]['error'][$key] == UPLOAD_ERR_OK) {
                        $image_name = time() . "_" . basename($_FILES[$file_key]['name'][$key]);
                        $upload_path = "../Uploads/" . $image_name;
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            $insert_image_query = "INSERT INTO tbl_product_images (product_id, color_id, image_url) VALUES (?, ?, ?)";
                            $image_stmt = $conn->prepare($insert_image_query);
                            $image_stmt->bind_param("iis", $id, $color_id, $image_name);
                            $image_stmt->execute();
                        }
                    }
                }
            }
        }

        // Xóa màu không còn trong danh sách mới
        foreach ($existing_colors as $color => $color_id) {
            if (!in_array($color, $new_colors)) {
                $conn->query("DELETE FROM tbl_product_colors WHERE product_id = $id AND color_id = $color_id");
            }
        }
    }

    // Chuyển hướng bằng JavaScript và lưu thông báo vào session để hiển thị toast
    $_SESSION['toast_message'] = $product_id ? "Sửa sản phẩm thành công!" : "Thêm sản phẩm thành công!";
    echo "<script>window.location.href='index.php?page=products';</script>";
    exit();
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id) {
        $delete_query = "DELETE FROM tbl_products WHERE product_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();
        // Các bảng liên quan sẽ tự động xóa nhờ ON DELETE CASCADE
        $_SESSION['toast_message'] = "Xóa sản phẩm thành công!";
        echo "<script>window.location.href='index.php?page=products';</script>";
        exit();
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    /* CSS cho toast */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }
</style>

<!-- Toast thông báo -->
<div class="toast-container">
    <?php if (isset($_SESSION['toast_message'])): ?>
        <div class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['toast_message']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['toast_message']); ?>
    <?php endif; ?>
</div>

<div class="container mt-4">
    <h2 class="text-center mb-4"><?= $product_id ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm' ?></h2>
    
    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product_name) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Chọn danh mục --</option>
                <?php
                $categories_query = "SELECT * FROM tbl_categories";
                $categories_result = $conn->query($categories_query);
                while ($cat = $categories_result->fetch_assoc()) {
                    $selected = ($category_id == $cat['category_id']) ? "selected" : "";
                    echo "<option value='{$cat['category_id']}' $selected>" . htmlspecialchars($cat['category_name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả sản phẩm</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product_description) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Số lượng trong kho</label>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($quantity ?: 0) ?>" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cấu hình cố định</label>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kích thước màn hình</label>
                    <input type="text" name="screen_size" class="form-control" value="<?= htmlspecialchars($product_specs['screen_size'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tần số quét</label>
                    <input type="text" name="refresh_rate" class="form-control" value="<?= htmlspecialchars($product_specs['refresh_rate'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hệ điều hành</label>
                    <input type="text" name="os" class="form-control" value="<?= htmlspecialchars($product_specs['os'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chipset</label>
                    <input type="text" name="chipset" class="form-control" value="<?= htmlspecialchars($product_specs['chipset'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Camera sau</label>
                    <input type="text" name="rear_camera" class="form-control" value="<?= htmlspecialchars($product_specs['rear_camera'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Camera trước</label>
                    <input type="text" name="front_camera" class="form-control" value="<?= htmlspecialchars($product_specs['front_camera'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pin</label>
                    <input type="text" name="battery" class="form-control" value="<?= htmlspecialchars($product_specs['battery'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Dung lượng & Giá</label>
            <div id="variant-container">
                <?php if ($product_id && !empty($product_variants)): ?>
                    <?php foreach ($product_variants as $index => $variant): ?>
                        <div class="variant-group d-flex align-items-center gap-2 mb-2">
                            <input type="text" name="rams[]" class="form-control" placeholder="RAM (VD: 8GB)" value="<?= htmlspecialchars($variant['ram']) ?>" required>
                            <input type="text" name="storages[]" class="form-control" placeholder="Dung lượng (VD: 256GB)" value="<?= htmlspecialchars($variant['storage']) ?>" required>
                            <input type="number" name="prices[]" class="form-control" placeholder="Giá" value="<?= htmlspecialchars($variant['price']) ?>" required>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeVariant(this)">❌</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="variant-group d-flex align-items-center gap-2 mb-2">
                        <input type="text" name="rams[]" class="form-control" placeholder="RAM (VD: 8GB)" required>
                        <input type="text" name="storages[]" class="form-control" placeholder="Dung lượng (VD: 256GB)" required>
                        <input type="number" name="prices[]" class="form-control" placeholder="Giá" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeVariant(this)">❌</button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-success mt-2" onclick="addVariant()">➕ Thêm Dung Lượng</button>
        </div>

        <div class="mb-3">
            <label class="form-label">Màu sắc & Ảnh</label>
            <div id="color-container">
                <?php if ($product_id && !empty($product_colors)): ?>
                    <?php $index = 0; ?>
                    <?php foreach ($product_colors as $color_id => $color): ?>
                        <div class="color-group mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="text" name="colors[]" class="form-control" value="<?= htmlspecialchars($color) ?>" required>
                                <input type="file" name="images_<?= $index ?>[]" class="form-control" multiple accept="image/*">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeColor(this)">❌</button>
                            </div>
                            <div class="existing-images d-flex flex-wrap gap-2">
                                <?php if (!empty($product_images[$color_id])): ?>
                                    <?php foreach ($product_images[$color_id] as $img): ?>
                                        <div class="image-item position-relative">
                                            <img src="../Uploads/<?= htmlspecialchars($img['image_url']) ?>" width="100" class="border rounded">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="deleteImage(<?= $img['image_id'] ?>, this)">x</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="color-group mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="text" name="colors[]" class="form-control" placeholder="Tên màu" required>
                            <input type="file" name="images_0[]" class="form-control" multiple accept="image/*">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeColor(this)">❌</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-success mt-2" onclick="addColor()">➕ Thêm Màu</button>
        </div>

        <button type="submit" name="save" class="btn btn-primary w-100">Lưu</button>
    </form>
</div>

<script>
let colorIndex = <?= $product_id && !empty($product_colors) ? count($product_colors) : 1 ?>;
let variantIndex = <?= $product_id && !empty($product_variants) ? count($product_variants) : 1 ?>;

function addColor() {
    let container = document.getElementById("color-container");
    let div = document.createElement("div");
    div.classList.add("color-group", "mb-3");
    div.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <input type="text" name="colors[]" class="form-control" placeholder="Tên màu" required>
            <input type="file" name="images_${colorIndex}[]" class="form-control" multiple accept="image/*">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeColor(this)">❌</button>
        </div>
    `;
    container.appendChild(div);
    colorIndex++;
}

function removeColor(btn) {
    btn.closest('.color-group').remove();
}

function addVariant() {
    let container = document.getElementById("variant-container");
    let div = document.createElement("div");
    div.classList.add("variant-group", "d-flex", "align-items-center", "gap-2", "mb-2");
    div.innerHTML = `
        <input type="text" name="rams[]" class="form-control" placeholder="RAM (VD: 8GB)" required>
        <input type="text" name="storages[]" class="form-control" placeholder="Dung lượng (VD: 256GB)" required>
        <input type="number" name="prices[]" class="form-control" placeholder="Giá" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeVariant(this)">❌</button>
    `;
    container.appendChild(div);
    variantIndex++;
}

function removeVariant(btn) {
    btn.parentElement.remove();
}

function deleteImage(imageId, btn) {
    if (confirm("Bạn có chắc chắn muốn xóa ảnh này?")) {
        fetch('delete_image.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'image_id=' + imageId
        }).then(response => response.text())
          .then(data => {
              if (data === 'success') {
                  btn.closest('.image-item').remove();
              } else {
                  alert('Xóa ảnh thất bại!');
              }
          }).catch(() => alert('Lỗi kết nối!'));
    }
}
</script>

<div class="container mt-4">
    <h2 class="text-center mb-3">Danh Sách Sản Phẩm</h2>
    
    <!-- Nút Thêm sản phẩm -->
    <div class="mb-3 text-end">
        <a href="?page=products" class="btn btn-primary">➕ Thêm Sản Phẩm</a>
    </div>

    <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 15%;">Tên</th>
                <th style="width: 20%;">Dung lượng & Giá</th>
                <th style="width: 20%;">Cấu hình</th>
                <th style="width: 10%;">Danh Mục</th>
                <th style="width: 15%;">Hình ảnh & Màu sắc</th>
                <th style="width: 5%;">Số lượng</th>
                <th style="width: 10%;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, c.category_name FROM tbl_products p 
                    LEFT JOIN tbl_categories c ON p.category_id = c.category_id";
            $result = $conn->query($sql);

            if (!$result) {
                echo "<tr><td colspan='8'>Lỗi truy vấn: " . $conn->error . "</td></tr>";
            } else {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['product_id'];

                    // Lấy thông số cố định
                    $spec_query = "SELECT * FROM tbl_product_specifications WHERE product_id = ? LIMIT 1";
                    $spec_stmt = $conn->prepare($spec_query);
                    $spec_stmt->bind_param("i", $product_id);
                    $spec_stmt->execute();
                    $spec_result = $spec_stmt->get_result();
                    $specs_html = "";
                    if ($spec = $spec_result->fetch_assoc()) {
                        $specs = [
                            "Kích thước màn hình" => $spec['screen_size'],
                            "Tần số quét" => $spec['refresh_rate'],
                            "Hệ điều hành" => $spec['os'],
                            "Chipset" => $spec['chipset'],
                            "Camera sau" => $spec['rear_camera'],
                            "Camera trước" => $spec['front_camera'],
                            "Pin" => $spec['battery']
                        ];
                        foreach ($specs as $key => $value) {
                            if (!empty($value)) {
                                $specs_html .= "<div><strong>$key</strong>: " . htmlspecialchars($value) . "</div>";
                            }
                        }
                    }

                    // Lấy biến thể
                    $variant_query = "SELECT ram, storage, price FROM tbl_product_variants WHERE product_id = ?";
                    $variant_stmt = $conn->prepare($variant_query);
                    $variant_stmt->bind_param("i", $product_id);
                    $variant_stmt->execute();
                    $variant_result = $variant_stmt->get_result();
                    $variants_html = "";
                    while ($variant = $variant_result->fetch_assoc()) {
                        $variants_html .= "<div><strong>" . htmlspecialchars($variant['ram']) . " / " . htmlspecialchars($variant['storage']) . "</strong>: " . number_format($variant['price'], 0, ',', '.') . " VNĐ</div>";
                    }
                    if (empty($variants_html)) {
                        $variants_html = "<span class='text-muted'>Không có dung lượng</span>";
                    }

                    // Lấy màu sắc và ảnh
                    $colors_query = "SELECT color_id, color_name FROM tbl_product_colors WHERE product_id = ?";
                    $colors_stmt = $conn->prepare($colors_query);
                    $colors_stmt->bind_param("i", $product_id);
                    $colors_stmt->execute();
                    $colors_result = $colors_stmt->get_result();
                    $images_colors_html = "";
                    $color_images = [];
                    $image_query = "SELECT color_id, image_url FROM tbl_product_images WHERE product_id = ?";
                    $image_stmt = $conn->prepare($image_query);
                    $image_stmt->bind_param("i", $product_id);
                    $image_stmt->execute();
                    $image_result = $image_stmt->get_result();
                    while ($img = $image_result->fetch_assoc()) {
                        $color_id = $img['color_id'] ?? 'no_color';
                        $color_images[$color_id][] = $img['image_url'];
                    }

                    if ($colors_result->num_rows > 0) {
                        while ($color = $colors_result->fetch_assoc()) {
                            $color_id = $color['color_id'];
                            $color_name = htmlspecialchars($color['color_name']);
                            $images_html = "";
                            if (!empty($color_images[$color_id])) {
                                foreach ($color_images[$color_id] as $image) {
                                    $image_path = "../Uploads/" . htmlspecialchars($image);
                                    $images_html .= file_exists($image_path) 
                                        ? "<img src='$image_path' width='50' class='border rounded shadow-sm m-1'>"
                                        : "<span class='text-danger'>Ảnh không tồn tại</span>";
                                }
                            } else {
                                $images_html = "<span class='text-muted'>Không có ảnh</span>";
                            }
                            $images_colors_html .= "<div class='mb-2 text-center'>
                                                        <div>$images_html</div>
                                                        <span class='badge bg-secondary'>$color_name</span>
                                                    </div>";
                        }
                    } else {
                        $images_html = !empty($color_images['no_color']) 
                            ? implode("", array_map(fn($img) => "<img src='../Uploads/" . htmlspecialchars($img) . "' width='50' class='border rounded shadow-sm m-1'>", $color_images['no_color']))
                            : "<span class='text-muted'>Không có ảnh</span>";
                        $images_colors_html = "<div class='mb-2 text-center'>$images_html</div>";
                    }

                    // Lấy quantity
                    $quantity = $row['quantity'];

                    echo "<tr>
                            <td>{$row['product_id']}</td>
                            <td>" . htmlspecialchars($row['product_name']) . "</td>
                            <td>$variants_html</td>
                            <td>$specs_html</td>
                            <td>" . htmlspecialchars($row['category_name']) . "</td>
                            <td><div class='images-colors-wrapper'>$images_colors_html</div></td>
                            <td>$quantity</td>
                            <td>
                                <a href='?page=products&edit=$product_id' class='btn btn-warning btn-sm'>Sửa</a>
                                <a href='?page=products&delete=$product_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn xóa?\")'>Xóa</a>
                            </td>
                        </tr>";
                }
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>