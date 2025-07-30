<?php
session_start();

// Đặt múi giờ cho Việt Nam (UTC+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối database
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<p>Vui lòng <a href='login_customer.php'>đăng nhập</a> để xem giỏ hàng.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách sản phẩm trong giỏ hàng
$sql = "
    SELECT 
        c.cart_id, c.product_id, c.quantity, c.color, c.storage, 
        p.product_name,
        pv.ram, pv.storage AS variant_storage, pv.price, pv.original_price,
        GROUP_CONCAT(DISTINCT pc.color_id) AS color_ids,
        GROUP_CONCAT(DISTINCT pc.color_name) AS available_colors,
        GROUP_CONCAT(DISTINCT pi.image_id) AS image_ids,
        GROUP_CONCAT(DISTINCT pi.image_url) AS image_urls
    FROM tbl_cart c
    JOIN tbl_products p ON c.product_id = p.product_id
    LEFT JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage
    LEFT JOIN tbl_product_colors pc ON p.product_id = pc.product_id
    LEFT JOIN tbl_product_images pi ON p.product_id = pi.product_id
    WHERE c.user_id = ?
    GROUP BY c.cart_id, c.product_id, c.quantity, c.color, c.storage, p.product_name, pv.ram, pv.storage, pv.price, pv.original_price
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Lưu trữ thông tin variants và Flash Sale
$product_variants = [];
$flashsale_info = [];
$product_ids = [];
while ($row = $result->fetch_assoc()) {
    $product_ids[] = $row['product_id'];
}
$result->data_seek(0); // Đặt con trỏ kết quả về đầu

if (!empty($product_ids)) {
    $product_ids_str = implode(',', array_unique($product_ids));

    // Lấy danh sách variants
    $variant_sql = "SELECT product_id, ram, storage, price FROM tbl_product_variants WHERE product_id IN ($product_ids_str)";
    $variant_result = $conn->query($variant_sql);
    while ($variant_row = $variant_result->fetch_assoc()) {
        $product_variants[$variant_row['product_id']][] = $variant_row;
    }

    // Lấy thông tin Flash Sale
    $flashsale_sql = "
        SELECT f.product_id, fs.flashsale_id, fs.title, f.max_quantity, f.sold_quantity
        FROM tbl_flashsales fs
        JOIN tbl_flashsale f ON fs.flashsale_id = f.flashsale_id
        WHERE f.product_id IN ($product_ids_str)
        AND fs.status = 'active'
        AND fs.start_time <= NOW()
        AND fs.end_time >= NOW()";
    $flashsale_result = $conn->query($flashsale_sql);
    while ($flashsale_row = $flashsale_result->fetch_assoc()) {
        $flashsale_info[$flashsale_row['product_id']] = $flashsale_row;
    }
}

// Xử lý mã giảm giá
// Xử lý mã giảm giá
$discount = 0;
$promo_code = '';
$promo_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $promo_code = trim($_POST['promo_code']);
    $promo_sql = "SELECT * FROM tbl_promotions WHERE promo_code = ? AND status = 'active' AND start_date <= NOW() AND end_date >= NOW()";
    $promo_stmt = $conn->prepare($promo_sql);
    $promo_stmt->bind_param("s", $promo_code);
    $promo_stmt->execute();
    $promo_result = $promo_stmt->get_result();

    if ($promo_result->num_rows > 0) {
        $promo = $promo_result->fetch_assoc();
        if ($promo['discount_type'] === 'percentage') {
            $discount = $promo['discount_value'] / 100;
            $promo_message = "Áp dụng mã {$promo_code} thành công! Giảm {$promo['discount_value']}%.";
        } else {
            $discount = $promo['discount_value'];
            $promo_message = "Áp dụng mã {$promo_code} thành công! Giảm " . number_format($promo['discount_value'], 0, ',', '.') . " VNĐ.";
        }
        // Lưu mã giảm giá và giá trị giảm vào session
        $_SESSION['promo_code'] = $promo_code;
        $_SESSION['discount'] = $discount;
        $_SESSION['discount_type'] = $promo['discount_type']; // Thêm dòng này
    } else {
        $promo_message = "Mã giảm giá không hợp lệ hoặc đã hết hạn.";
        // Xóa thông tin mã giảm giá khỏi session nếu không hợp lệ
        unset($_SESSION['promo_code']);
        unset($_SESSION['discount']);
        unset($_SESSION['discount_type']);
    }
    $promo_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        /* Reset và thiết lập cơ bản */
        * { box-sizing: border-box; }
        h2 { font-weight: 600; color: #333; padding-bottom: 20px; text-align: center; font-size: 20px; }

        /* Mobile-first: Thiết kế cho màn hình nhỏ trước */
        .cart-table {
            width: 100%; border-collapse: separate; border-spacing: 0; background-color: #fff; border-radius: 10px; overflow: hidden;
        }
        .cart-table th, .cart-table td { padding: 10px; text-align: left; border-bottom: 1px solid #e9ecef; display: block; }
        .cart-table th { background-color: #007bff; color: #fff; font-weight: 500; text-transform: uppercase; display: none; }
        .cart-table td { font-weight: 400; color: #555; }
        .cart-table tr:last-child td { border-bottom: none; }
        .cart-table tr { padding: 10px 0; border-bottom: 1px solid #e9ecef; display: block; }
        .cart-table tr:hover { background-color: #f1f3f5; }
        .product-image { width: 80px; height: auto; border-radius: 8px; display: block; margin: 0 auto; }
        .flashsale-badge { position: absolute;left: 5px; width: 50px; z-index: 10; }
        .cart-table td:nth-child(2) { text-align: center; padding: 10px 0; }
        .productname { font-weight: 700; font-size: 16px; padding-bottom: 5px; }
        .price { font-size: 14px; color: #dc3545; font-weight: 600; padding-bottom: 5px; }
        .original-price { font-size: 12px; color: #999; text-decoration: line-through; margin-right: 5px; }
        select { width: 100%; max-width: 120px; border: 1px solid #ced4da; background-color: #fff; font-size: 14px; text-align: center; color: #333; font-weight: 600; cursor: pointer; margin: 0 auto; display: block; }
        select:focus { border-color: #000; box-shadow: 0 0 5px rgba(0, 123, 255, 0.3); outline: none; }
        select:hover { border-color: #000; }
        .quantity-control { display: flex; align-items: center; justify-content: center; gap: 5px; padding: 10px 0; }
        .quantity-btn { width: 35px; height: 35px; background-color: #000; color: #fff; border: none; font-size: 16px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .quantity-btn:hover { background-color: #363636; }
        .quantity-btn:disabled { background-color: #ced4da; cursor: not-allowed; }
        .quantity-input { background: transparent; width: 36px; text-align: center; border: none; border-radius: 5px; font-size: 19px; color: #333; }
        .cart-table td:nth-child(4) { text-align: center; font-size: 14px; font-weight: 600; color: #dc3545; padding: 10px 0; }
        .cart-table td:last-child { display: flex; justify-content: center; padding: 10px; }
        .remove-btn { background-color: #000; color: #fff; padding: 8px 15px; border: none; font-size: 14px; font-weight: 500; cursor: pointer; width: 100%; max-width: 200px; text-align: center; }
        .remove-btn:hover { background-color: #fff; color: #000; }
        .total-section { padding-top: 20px; text-align: center; }
        .total-section h4 { font-weight: 600; color: #dc3545; padding-bottom: 10px; font-size: 16px; }
        .btn-primary { background-color: #000; border: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; font-size: 16px; width: 100%; max-width: 300px; display: block; margin: 0 auto; }
        .btn-primary:hover { background-color: #3e3e3e; }
        .empty-cart { text-align: center; padding: 30px 0; color: #777; }
        .empty-cart .btn-primary { padding: 10px 20px; font-size: 14px; max-width: 200px; }
        .promo-section { margin-bottom: 20px; display: flex; gap: 10px; }
        .promo-section input { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px; }
        .promo-section input:focus { outline: none; }
        .promo-section button { background-color: #000; color: #fff; border: none; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; }
        .promo-section button:hover { background-color: #3e3e3e; }
        .formpromo { display: flex; justify-content: flex-start; }
        .toast { position: fixed; top: 20px; right: 20px; min-width: 250px; padding: 15px; border-radius: 5px; color: #fff; font-size: 14px; z-index: 1000; opacity: 0; transition: opacity 0.3s ease, transform 0.3s ease; transform: translateY(-20px); }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success { background-color: #28a745; }
        .toast.error { background-color: #dc3545; }
        .toast .close-btn { position: absolute; top: 5px; right: 10px; cursor: pointer; font-size: 16px; color: #fff; }

        /* Responsive: Tablet (576px trở lên) */
        @media (min-width: 576px) {
            .cart-table th, .cart-table td { display: table-cell; padding: 12px; }
            .cart-table th { display: table-cell; }
            .cart-table tr { display: table-row; padding: 0; }
            .product-image { width: 90px; }
            .cart-table td:nth-child(2) { text-align: left; padding: 12px; }
            .productname { font-size: 18px; padding-bottom: 0; }
            .price { font-size: 16px; padding-bottom: 0; }
            select { margin: 0; }
            .quantity-control { padding: 0; }
            .cart-table td:nth-child(4) { text-align: left; font-size: 16px; }
            .cart-table td:last-child { display: table-cell; padding: 12px; }
            .remove-btn { width: auto; }
            .total-section { text-align: right; }
            .total-section h4 { font-size: 18px; }
            .btn-primary { width: auto; max-width: none; }
            .empty-cart { padding: 40px 0; }
            .empty-cart .btn-primary { padding: 12px 30px; font-size: 16px; }
            .promo-section { justify-content: flex-end; }
        }

        /* Responsive: Desktop (768px trở lên) */
        @media (min-width: 768px) {
            h2 { font-size: 24px; }
            .product-image { width: 100px; }
            .productname { font-size: 20px; }
            .price { font-size: 18px; }
            .cart-table td:nth-child(4) { font-size: 18px; }
            .total-section { padding-top: 30px; }
            .total-section h4 { font-size: 20px; padding-bottom: 15px; }
            .empty-cart { padding: 50px 0; }
        }
    </style>
</head>
<body>
    <h2>Giỏ Hàng Của Bạn</h2>
    <div id="toast" class="toast"></div>

    <?php if ($result->num_rows > 0): ?>
        <table class="cart-table">
            
            <tbody>
                <?php 
                $total_amount = 0;
                while ($row = $result->fetch_assoc()): 
                    // Chuyển danh sách màu và hình ảnh thành mảng
                    $colors = $row['available_colors'] ? explode(',', $row['available_colors']) : [];
                    $color_ids = $row['color_ids'] ? explode(',', $row['color_ids']) : [];
                    $image_urls = $row['image_urls'] ? explode(',', $row['image_urls']) : [];
                    $image_ids = $row['image_ids'] ? explode(',', $row['image_ids']) : [];

                    // Lấy danh sách variants từ mảng $product_variants
                    $variants = isset($product_variants[$row['product_id']]) ? $product_variants[$row['product_id']] : [];

                    // Lấy giá từ tbl_product_variants
                    $price = $row['price'];
                    $original_price = $row['original_price'];

                    // Kiểm tra Flash Sale
                    $is_flashsale = isset($flashsale_info[$row['product_id']]);
                    $remaining_quantity = $is_flashsale ? $flashsale_info[$row['product_id']]['max_quantity'] - $flashsale_info[$row['product_id']]['sold_quantity'] : null;

                    // Tính tổng phụ
                    $subtotal = $price * $row['quantity'];
                    $total_amount += $subtotal;

                    // Tìm hình ảnh tương ứng với màu đã chọn
                    $selected_color = $row['color'];
                    $selected_image_url = '';
                    if ($selected_color) {
                        $color_index = array_search($selected_color, $colors);
                        if ($color_index !== false && isset($color_ids[$color_index])) {
                            $color_id = $color_ids[$color_index];
                            $image_sql = "SELECT image_url FROM tbl_product_images WHERE product_id = ? AND color_id = ?";
                            $image_stmt = $conn->prepare($image_sql);
                            $image_stmt->bind_param("ii", $row['product_id'], $color_id);
                            $image_stmt->execute();
                            $image_result = $image_stmt->get_result();
                            if ($image_result->num_rows > 0) {
                                $selected_image_url = $image_result->fetch_assoc()['image_url'];
                            }
                            $image_stmt->close();
                        }
                    }
                    if (!$selected_image_url && !empty($image_urls)) {
                        $selected_image_url = $image_urls[0];
                    }
                ?>
                    <tr data-cart-id="<?php echo $row['cart_id']; ?>">
                        <td style="position: relative;">
                            <?php if ($is_flashsale): ?>
                                <img src="public/images/flashsale.webp" alt="Flash Sale" class="flashsale-badge">
                            <?php endif; ?>
                            <?php if ($selected_image_url): ?>
                                <img src="Uploads/<?php echo htmlspecialchars($selected_image_url); ?>" alt="Product Image" class="product-image">
                            <?php else: ?>
                                Không có hình ảnh
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="productname"><?php echo htmlspecialchars($row['product_name']); ?></div>
                            <div class="price">
                                <?php if ($is_flashsale && $original_price > $price): ?>
                                    <span class="original-price"><?php echo number_format($original_price, 0, ',', '.'); ?> VNĐ</span>
                                    <?php echo number_format($price, 0, ',', '.'); ?> VNĐ
                                <?php else: ?>
                                    <?php echo number_format($price, 0, ',', '.'); ?> VNĐ
                                <?php endif; ?>
                                <?php if ($is_flashsale): ?>
                                    <div class="remaining-quantity">Còn: <?php echo $remaining_quantity; ?> sản phẩm</div>
                                <?php endif; ?>
                            </div>
                            <select name="color" class="cart-color" data-cart-id="<?php echo $row['cart_id']; ?>">
                                <?php foreach ($colors as $color): ?>
                                    <option value="<?php echo $color; ?>" <?php echo $color === $row['color'] ? 'selected' : ''; ?>>
                                        <?php echo $color; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="storage" class="cart-storage" data-cart-id="<?php echo $row['cart_id']; ?>">
                                <?php foreach ($variants as $variant): ?>
                                    <option value="<?php echo $variant['storage']; ?>" <?php echo $variant['storage'] === $row['storage'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($variant['ram'] . ' / ' . $variant['storage']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $row['cart_id']; ?>, -1)" <?php echo $row['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="<?php echo $row['quantity']; ?>" min="1" readonly data-max="<?php echo $is_flashsale ? $remaining_quantity : PHP_INT_MAX; ?>">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $row['cart_id']; ?>, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td class="subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                        <td>
                            <button type="button" class="remove-btn" onclick="removeCartItem(<?php echo $row['cart_id']; ?>)">Xóa</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="total-section">
            <div class="promo-section">
                <form id="promo-form" class="formpromo">
                    <input type="text" name="promo_code" placeholder="Nhập mã giảm giá" required>
                    <button type="submit" name="apply_promo">Áp dụng</button>
                </form>
            </div>
            <?php if ($discount > 0 && !empty($promo_code)): ?>
                <p style="color: #28a745; font-weight: 500; margin-bottom: 10px;">
                    Bạn đang sử dụng mã giảm giá <strong><?php echo htmlspecialchars($promo_code); ?></strong> và được giảm 
                    <strong><?php echo number_format($discount, 0, ',', '.'); ?> VNĐ</strong>.
                </p>
            <?php endif; ?>
            <h4 id="total-amount">
                Tổng Cộng: 
                <?php 
                $final_amount = $discount > 0 && $promo_message !== 'Mã giảm giá không hợp lệ hoặc đã hết hạn.' ? ($promo_message !== '' ? ($promo_message !== 'Mã giảm giá không hợp lệ hoặc đã hết hạn.' ? ($promo['discount_type'] === 'percentage' ? $total_amount * (1 - $discount) : $total_amount - $discount) : $total_amount) : $total_amount) : $total_amount;
                if ($discount > 0 && $promo_message !== 'Mã giảm giá không hợp lệ hoặc đã hết hạn.') {
                    echo '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ';
                }
                echo number_format($final_amount, 0, ',', '.') . ' VNĐ';
                ?>
            </h4>
<a href="orders.php?promo_code=<?php echo urlencode($promo_code); ?>" class="btn btn-primary">Thanh Toán</a>        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p>Giỏ hàng của bạn đang trống. Hãy tiếp tục mua sắm!</p>
        </div>
    <?php endif; ?>

    <script>
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        async function updateCart(cartId, data) {
            const formData = new FormData();
            formData.append('action', 'update_cart');
            formData.append('cart_id', cartId);
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            try {
                const response = await fetch('views/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
                    const subtotalElement = row.querySelector('.subtotal');
                    const quantityInput = row.querySelector('.quantity-input');
                    const minusButton = row.querySelector('.quantity-btn:first-child');

                    // Cập nhật giá và số lượng
                    subtotalElement.textContent = parseInt(result.subtotal).toLocaleString('vi-VN') + ' VNĐ';
                    quantityInput.value = result.quantity;
                    minusButton.disabled = result.quantity <= 1;

                    // Cập nhật tổng cộng
                    const totalAmountElement = document.getElementById('total-amount');
                    totalAmountElement.innerHTML = `Tổng Cộng: ${result.total_amount_html}`;

                    showToast(result.message, 'success');
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Đã xảy ra lỗi khi xử lý yêu cầu.', 'error');
            }
        }

        async function removeCartItem(cartId) {
            const formData = new FormData();
            formData.append('action', 'remove_cart');
            formData.append('cart_id', cartId);

            try {
                const response = await fetch('views/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
                    row.remove();

                    // Cập nhật tổng cộng
                    const totalAmountElement = document.getElementById('total-amount');
                    totalAmountElement.innerHTML = `Tổng Cộng: ${result.total_amount_html}`;

                    // Kiểm tra nếu giỏ hàng trống
                    if (!document.querySelector('.cart-table tbody tr')) {
                        document.querySelector('.cart-table').remove();
                        document.querySelector('.total-section').remove();
                        document.querySelector('h2').insertAdjacentHTML('afterend', `
                            <div class="empty-cart">
                                <p>Giỏ hàng của bạn đang trống. Hãy tiếp tục mua sắm!</p>
                                <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                            </div>
                        `);
                    }

                    showToast(result.message, 'success');
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Đã xảy ra lỗi khi xử lý yêu cầu.', 'error');
            }
        }

        async function updateQuantity(cartId, change) {
            const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
            const quantityInput = row.querySelector('.quantity-input');
            let quantity = parseInt(quantityInput.value) + change;
            const maxQuantity = parseInt(quantityInput.dataset.max);

            if (quantity < 1) quantity = 1;
            if (maxQuantity && quantity > maxQuantity) {
                showToast(`Chỉ còn ${maxQuantity} sản phẩm trong Flash Sale!`, 'error');
                return;
            }

            await updateCart(cartId, {
                quantity: quantity,
                color: row.querySelector('.cart-color').value,
                storage: row.querySelector('.cart-storage').value
            });
        }

        document.querySelectorAll('.cart-color, .cart-storage').forEach(select => {
            select.addEventListener('change', async function() {
                const cartId = this.dataset.cartId;
                const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
                await updateCart(cartId, {
                    quantity: row.querySelector('.quantity-input').value,
                    color: row.querySelector('.cart-color').value,
                    storage: row.querySelector('.cart-storage').value
                });
            });
        });

        document.getElementById('promo-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'apply_promo');

            try {
                const response = await fetch('views/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    const totalAmountElement = document.getElementById('total-amount');
                    totalAmountElement.innerHTML = `Tổng Cộng: ${result.total_amount_html}`;
                    if (result.promo_info) {
                        const promoSection = document.querySelector('.promo-section');
                        promoSection.insertAdjacentHTML('afterend', `
                            <p style="color: #28a745; font-weight: 500; margin-bottom: 10px;">
                                Bạn đang sử dụng mã giảm giá <strong>${result.promo_info.promo_code}</strong> và được giảm 
                                <strong>${parseInt(result.promo_info.discount).toLocaleString('vi-VN')} VNĐ</strong>.
                            </p>
                        `);
                    }
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Đã xảy ra lỗi khi xử lý mã giảm giá.', 'error');
            }
        });

        // Hiển thị toast nếu có thông báo từ PHP
        <?php if ($promo_message): ?>
            showToast("<?php echo addslashes($promo_message); ?>", "<?php echo strpos($promo_message, 'thành công') !== false ? 'success' : 'error'; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>