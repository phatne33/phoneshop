<?php
// Thiết lập session với bảo mật
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra vai trò admin
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'phonedb');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

    // Kiểm tra giá trị hợp lệ
    $valid_statuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
    if ($order_id === false || $order_id <= 0 || !in_array($status, $valid_statuses)) {
        $error_message = "Dữ liệu không hợp lệ.";
    } else {
        $sql_update = "UPDATE tbl_orders SET status = ? WHERE order_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $status, $order_id);

        if ($stmt_update->execute()) {
            $_SESSION['toast_message'] = "Cập nhật trạng thái đơn hàng thành công!";
            echo "<script>window.location.href='index.php?page=orderlist';</script>";
            exit();
        } else {
            $error_message = "Lỗi khi cập nhật trạng thái: " . $conn->error;
        }
        $stmt_update->close();
    }
}

// Hàm lấy tên tỉnh, huyện, xã từ mã
function getNameFromCode($type, $code, $json_data) {
    if (!$code) return 'N/A';
    switch ($type) {
        case 'province':
            foreach ($json_data as $item) {
                if ($item['code'] == $code) return $item['name'];
            }
            break;
        case 'district':
            foreach ($json_data as $province) {
                foreach ($province['districts'] as $item) {
                    if ($item['code'] == $code) return $item['name'];
                }
            }
            break;
        case 'commune':
            foreach ($json_data as $province) {
                foreach ($province['districts'] as $district) {
                    foreach ($district['wards'] as $item) {
                        if ($item['code'] == $code) return $item['name'];
                    }
                }
            }
            break;
    }
    return $code; // Trả về mã nếu không tìm thấy
}

// Tải dữ liệu JSON cục bộ
$json_data = json_decode(file_get_contents('provinces.json'), true);
if (!$json_data) {
    $error_message = "Không thể tải dữ liệu tỉnh, huyện, xã.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Phong cách giống Tailwind CSS */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6; /* bg-gray-100 */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1600px; /* Tăng chiều rộng để chứa cột mới */
            margin: 0 auto;
            padding: 2rem; /* p-8 */
        }
        .header {
            font-size: 1.875rem; /* text-3xl */
            font-weight: 700; /* font-bold */
            color: #1f2937; /* text-gray-800 */
            margin-bottom: 1.5rem; /* mb-6 */
        }
        .table-wrapper {
            background: #ffffff; /* bg-white */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* shadow-sm */
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 1rem; /* p-4 */
            text-align: left;
            border-bottom: 1px solid #e5e7eb; /* border-b border-gray-200 */
        }
        .table th {
            background-color: #f9fafb; /* bg-gray-50 */
            font-weight: 600; /* font-semibold */
            color: #374151; /* text-gray-700 */
        }
        .table td {
            color: #4b5563; /* text-gray-600 */
        }
        .status-select {
            padding: 0.5rem; /* p-2 */
            border: 1px solid #d1d5db; /* border border-gray-300 */
            border-radius: 0.375rem; /* rounded-md */
            background-color: #ffffff; /* bg-white */
            color: #374151; /* text-gray-700 */
            width: 100%;
        }
        .status-select:focus {
            outline: none;
            border-color: #3b82f6; /* focus:border-blue-500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); /* focus:ring focus:ring-blue-500 */
        }
        .update-btn {
            background-color: #3b82f6; /* bg-blue-500 */
            color: #ffffff; /* text-white */
            padding: 0.5rem 1rem; /* py-2 px-4 */
            border-radius: 0.375rem; /* rounded-md */
            border: none;
            cursor: pointer;
        }
        .update-btn:hover {
            background-color: #2563eb; /* hover:bg-blue-600 */
        }
        .success-message {
            background-color: #d1fae5; /* bg-green-100 */
            color: #065f46; /* text-green-800 */
            padding: 1rem; /* p-4 */
            border-radius: 0.375rem; /* rounded-md */
            margin-bottom: 1.5rem; /* mb-6 */
            text-align: center;
        }
        .error-message {
            background-color: #fee2e2; /* bg-red-100 */
            color: #991b1b; /* text-red-800 */
            padding: 1rem; /* p-4 */
            border-radius: 0.375rem; /* rounded-md */
            margin-bottom: 1.5rem; /* mb-6 */
            text-align: center;
        }
        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .product-item {
            display: flex;
            align-items: center;
            gap: 0.5rem; /* gap-2 */
            margin-bottom: 0.5rem; /* mb-2 */
        }
        .product-item img {
            width: 40px;
            height: auto;
            border-radius: 0.25rem; /* rounded */
        }
        .product-info {
            flex: 1;
        }
        .product-info p {
            margin: 0;
            font-size: 0.875rem; /* text-sm */
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
        @media (max-width: 768px) {
            .container {
                padding: 1rem; /* p-4 */
            }
            .header {
                font-size: 1.5rem; /* text-2xl */
            }
            .table th, .table td {
                padding: 0.75rem; /* p-3 */
                font-size: 0.875rem; /* text-sm */
            }
            .update-btn {
                padding: 0.375rem 0.75rem; /* py-1.5 px-3 */
                font-size: 0.875rem; /* text-sm */
            }
            .product-item img {
                width: 30px;
            }
            .product-info p {
                font-size: 0.75rem; /* text-xs */
            }
        }
    </style>
</head>
<body>
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

    <div class="container">
        <h1 class="header">Danh Sách Đơn Hàng</h1>

        <!-- Thông báo -->
        <?php
        if (isset($error_message)) {
            echo '<p class="error-message">' . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        ?>

        <!-- Bảng danh sách đơn hàng -->
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Đơn Hàng</th>
                        <th>Khách Hàng</th>
                        <th>Sản Phẩm</th>
                        <th>Dung lượng</th>
                        <th>Địa chỉ</th>
                        <th>Tổng Tiền</th>
                        <th>Phí Vận Chuyển</th>
                        <th>Phương Thức Thanh Toán</th>
                        <th>Phương Thức Vận Chuyển</th>
                        <th>Ghi Chú</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_orders = "SELECT o.*, u.full_name, a.province, a.district, a.commune, a.hamlet, a.address_detail 
                                   FROM tbl_orders o 
                                   LEFT JOIN tbl_users u ON o.user_id = u.user_id 
                                   LEFT JOIN tbl_addresses a ON o.address_id = a.address_id 
                                   ORDER BY o.order_date DESC";
                    $result_orders = $conn->query($sql_orders);

                    if ($result_orders->num_rows > 0) {
                        while ($row = $result_orders->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . '</td>';
                            echo '<td>' . htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') . '</td>';

                            // Cột Sản Phẩm
                            echo '<td>';
                            $sql_products = "SELECT od.*, p.product_name, 
                                            (SELECT pi.image_url 
                                             FROM tbl_product_images pi 
                                             WHERE pi.product_id = od.product_id 
                                             LIMIT 1) AS image,
                                            (SELECT pc.color_name 
                                             FROM tbl_product_colors pc 
                                             WHERE pc.product_id = od.product_id 
                                             LIMIT 1) AS color
                                     FROM tbl_order_details od 
                                     LEFT JOIN tbl_products p ON od.product_id = p.product_id 
                                     WHERE od.order_id = ?";
                            $stmt_products = $conn->prepare($sql_products);
                            $stmt_products->bind_param("i", $row['order_id']);
                            $stmt_products->execute();
                            $result_products = $stmt_products->get_result();

                            if ($result_products->num_rows > 0) {
                                echo '<ul class="product-list">';
                                while ($product = $result_products->fetch_assoc()) {
                                    $image_path = $product['image'] ? 'http://localhost/phone-shop/uploads/' . $product['image'] : 'uploads/default-image.jpg';
                                    echo '<li class="product-item">';
                                    echo '<img src="' . htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . '">';
                                    echo '<div class="product-info">';
                                    echo '<p><strong>' . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . '</strong></p>';
                                    echo '<p>Màu: ' . ($product['color'] ? htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8') : 'N/A') . '</p>';
                                    echo '<p>Số lượng: ' . htmlspecialchars($product['quantity'], ENT_QUOTES, 'UTF-8') . '</p>';
                                    echo '</div>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo 'Không có sản phẩm';
                            }
                            echo '</td>';

                            // Cột Dung lượng
                            echo '<td>';
                            if ($result_products->num_rows > 0) {
                                $result_products->data_seek(0); // Reset con trỏ kết quả để duyệt lại
                                echo '<ul class="product-list">';
                                while ($product = $result_products->fetch_assoc()) {
                                    echo '<li class="product-item">';
                                    echo '<p>' . ($product['storage'] ? htmlspecialchars($product['storage'], ENT_QUOTES, 'UTF-8') : 'N/A') . '</p>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo 'N/A';
                            }
                            echo '</td>';

                            $stmt_products->close();

                            // Cột Địa chỉ
                            echo '<td>';
                            if ($row['address_id']) {
                                $province_name = getNameFromCode('province', $row['province'], $json_data);
                                $district_name = getNameFromCode('district', $row['district'], $json_data);
                                $commune_name = getNameFromCode('commune', $row['commune'], $json_data);
                                $full_address = htmlspecialchars($row['address_detail']) . ', ' .
                                                htmlspecialchars($row['hamlet']) . ', ' .
                                                htmlspecialchars($commune_name) . ', ' .
                                                htmlspecialchars($district_name) . ', ' .
                                                htmlspecialchars($province_name);
                                echo $full_address;
                            } else {
                                echo 'N/A';
                            }
                            echo '</td>';

                            echo '<td>' . number_format($row['total_amount'], 0, ',', '.') . ' VNĐ</td>';
                            echo '<td>' . number_format($row['shipping_fee'] ?? 0, 0, ',', '.') . ' VNĐ</td>';
                            echo '<td>' . htmlspecialchars($row['payment_method'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</td>';
                            echo '<td>' . htmlspecialchars($row['shipping_method'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</td>';
                            echo '<td>' . ($row['notes'] ? htmlspecialchars($row['notes'], ENT_QUOTES, 'UTF-8') : 'Không có') . '</td>';
                            echo '<td>';
                            echo '<form method="POST" action="">';
                            echo '<input type="hidden" name="order_id" value="' . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . '">';
                            echo '<select name="status" class="status-select">';
                            $statuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
                            foreach ($statuses as $status) {
                                $selected = ($row['status'] == $status) ? 'selected' : '';
                                echo '<option value="' . $status . '" ' . $selected . '>' . ucfirst($status) . '</option>';
                            }
                            echo '</select>';
                            echo '</td>';
                            echo '<td><button type="submit" class="update-btn">Cập Nhật</button></td>';
                            echo '</form>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="12" class="text-center">Chưa có đơn hàng nào.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>