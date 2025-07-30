<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo "<p>Vui lòng đăng nhập để xem danh sách yêu thích.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý yêu cầu xóa khỏi danh sách yêu thích bằng AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_favorite'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $product_id = (int)$_POST['product_id'];
    $delete_sql = "DELETE FROM tbl_favorites WHERE user_id = ? AND product_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if ($delete_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
        exit();
    }
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    $delete_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích!']);
    exit();
}

// Xử lý yêu cầu thêm vào giỏ hàng bằng AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $product_id = (int)$_POST['product_id'];

    // Lấy màu sắc đầu tiên từ tbl_product_colors
    $color_sql = "SELECT color_name FROM tbl_product_colors WHERE product_id = ? ORDER BY color_id ASC LIMIT 1";
    $color_stmt = $conn->prepare($color_sql);
    if ($color_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn màu sắc: ' . $conn->error]);
        exit();
    }
    $color_stmt->bind_param("i", $product_id);
    $color_stmt->execute();
    $color_result = $color_stmt->get_result();
    $color = $color_result->num_rows > 0 ? $color_result->fetch_assoc()['color_name'] : null;

    // Lấy dung lượng và giá đầu tiên từ tbl_product_variants
    $variant_sql = "SELECT storage, price FROM tbl_product_variants WHERE product_id = ? ORDER BY variant_id ASC LIMIT 1";
    $variant_stmt = $conn->prepare($variant_sql);
    if ($variant_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn biến thể: ' . $conn->error]);
        exit();
    }
    $variant_stmt->bind_param("i", $product_id);
    $variant_stmt->execute();
    $variant_result = $variant_stmt->get_result();
    $variant_row = $variant_result->num_rows > 0 ? $variant_result->fetch_assoc() : null;
    $storage = $variant_row ? $variant_row['storage'] : null;
    $price = $variant_row ? $variant_row['price'] : 0;

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $check_cart_sql = "SELECT cart_id FROM tbl_cart WHERE user_id = ? AND product_id = ? AND color = ? AND storage = ?";
    $check_cart_stmt = $conn->prepare($check_cart_sql);
    if ($check_cart_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn giỏ hàng: ' . $conn->error]);
        exit();
    }
    $check_cart_stmt->bind_param("iiss", $user_id, $product_id, $color, $storage);
    $check_cart_stmt->execute();
    $check_cart_result = $check_cart_stmt->get_result();

    if ($check_cart_result->num_rows > 0) {
        // Nếu sản phẩm đã có trong giỏ hàng, tăng số lượng
        $update_cart_sql = "UPDATE tbl_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ? AND color = ? AND storage = ?";
        $update_cart_stmt = $conn->prepare($update_cart_sql);
        if ($update_cart_stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn cập nhật giỏ hàng: ' . $conn->error]);
            exit();
        }
        $update_cart_stmt->bind_param("iiss", $user_id, $product_id, $color, $storage);
        $update_cart_stmt->execute();
    } else {
        // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
        $insert_cart_sql = "INSERT INTO tbl_cart (user_id, product_id, quantity, color, storage) VALUES (?, ?, 1, ?, ?)";
        $insert_cart_stmt = $conn->prepare($insert_cart_sql);
        if ($insert_cart_stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn thêm giỏ hàng: ' . $conn->error]);
            exit();
        }
        $insert_cart_stmt->bind_param("iiss", $user_id, $product_id, $color, $storage);
        $insert_cart_stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng!']);
    exit();
}

// Lấy danh sách yêu thích
$sql = "
    SELECT 
        p.product_id, p.product_name,
        (SELECT image_url FROM tbl_product_images WHERE product_id = p.product_id ORDER BY image_id ASC LIMIT 1) AS image_url,
        (SELECT price FROM tbl_product_variants WHERE product_id = p.product_id ORDER BY variant_id ASC LIMIT 1) AS variant_price
    FROM tbl_favorites f 
    JOIN tbl_products p ON f.product_id = p.product_id 
    WHERE f.user_id = ?
";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        h3 {
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .favorites-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .favorites-table th, .favorites-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .favorites-table th {
            background-color: #007bff;
            color: #fff;
            font-weight: 500;
            text-transform: uppercase;
        }

        .favorites-table td {
            font-weight: 400;
            color: #555;
        }

        .favorites-table tr:last-child td {
            border-bottom: none;
        }

        .favorites-table tr:hover {
            background-color: #f1f3f5;
        }

        .product-image {
            width: 100px;
            height: auto;
            border-radius: 8px;
        }

        .productname {
            font-weight: 700;
        }

        .price {
            color: #dc3545;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .action-btn {
            background-color: transparent;
            border: none;
            padding: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .add-to-cart-btn {
            color: #28a745;
        }

        .add-to-cart-btn:hover {
            background-color: #28a745;
            color: #fff;
        }

        .remove-btn {
            color: #dc3545;
        }

        .remove-btn:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .empty-message {
            text-align: center;
            padding: 50px 0;
            color: #777;
        }

        @media (min-width: 576px) {
            .favorites-table th,
            .favorites-table td {
                display: table-cell;
                padding: 12px;
            }

            .favorites-table th {
                display: table-cell;
            }

            .favorites-table tr {
                display: table-row;
                padding: 0;
            }

            .product-image {
                width: 90px;
            }

            .productname {
                font-size: 18px;
                text-align: left;
                padding: 0;
            }

            .price {
                font-size: 16px;
                text-align: left;
                padding-bottom: 0;
            }

            .favorites-table td:last-child {
                flex-direction: row;
                gap: 10px;
                justify-content: flex-start;
                align-items: center;
            }

            .action-btn {
                width: 40px;
                height: 40px;
            }
        }

        @media (min-width: 768px) {
            body {
                padding: 40px;
            }

            h3 {
                padding-bottom: 30px;
                font-size: 24px;
            }

            .favorites-table th,
            .favorites-table td {
                padding: 15px;
            }

            .product-image {
                width: 100px;
            }

            .productname {
                font-size: 20px;
            }

            .price {
                font-size: 18px;
            }

            .empty-message {
                padding: 50px 0;
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            .action-btn {
                font-size: 16px;
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>
    <h3>Danh sách yêu thích</h3>

    <?php if ($result->num_rows == 0): ?>
        <div class="empty-message">
            <p>Chưa có sản phẩm nào trong danh sách yêu thích.</p>
        </div>
    <?php else: ?>
        <table class="favorites-table">
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['image_url']): ?>
                                <img src="Uploads/<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image" class="product-image">
                            <?php else: ?>
                                Không có hình ảnh
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="productname"><?php echo htmlspecialchars($row['product_name']); ?></div>
                        </td>
                        <td>
                            <div class="price"><?php echo number_format($row['variant_price'] ?? 0, 0, ',', '.') . ' VNĐ'; ?></div>
                        </td>
                        <td class="action-buttons">
                            <form class="add-to-cart-form" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="action-btn add-to-cart-btn" title="Chuyển qua giỏ hàng">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                            <form class="remove-favorite-form" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="remove_favorite" value="1">
                                <button type="submit" class="action-btn remove-btn" title="Xóa khỏi yêu thích">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Xử lý xóa khỏi danh sách yêu thích bằng AJAX
        document.querySelectorAll('.remove-favorite-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const productRow = form.closest('tr');
                        if (productRow) {
                            productRow.remove();
                        }

                        const remainingProducts = document.querySelectorAll('.favorites-table tbody tr');
                        if (remainingProducts.length === 0) {
                            const container = document.querySelector('.favorites-table').parentElement;
                            container.innerHTML = '<div class="empty-message"><p>Chưa có sản phẩm nào trong danh sách yêu thích.</p></div>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi xóa sản phẩm!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            });
        });

        // Xử lý thêm vào giỏ hàng bằng AJAX
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>