<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8mb4");

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

if (!isset($_SESSION['user_id']) && !in_array($_GET['action'] ?? '', ['fetch_cart', 'fetch_favorites'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thực hiện hành động này.', 'redirect' => 'login_customer.php']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_cart':
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            exit();
        }

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

        $product_variants = [];
        $flashsale_info = [];
        $product_ids = [];
        while ($row = $result->fetch_assoc()) {
            $product_ids[] = $row['product_id'];
        }
        $result->data_seek(0);

        if (!empty($product_ids)) {
            $product_ids_str = implode(',', array_unique($product_ids));
            $variant_sql = "SELECT product_id, ram, storage, price FROM tbl_product_variants WHERE product_id IN ($product_ids_str)";
            $variant_result = $conn->query($variant_sql);
            while ($variant_row = $variant_result->fetch_assoc()) {
                $product_variants[$variant_row['product_id']][] = $variant_row;
            }

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

        ob_start();
        if ($result->num_rows > 0):
        ?>
            <table class="cart-table">
                <tbody id="cart-items">
                    <?php 
                    $total_amount = 0;
                    while ($row = $result->fetch_assoc()): 
                        $colors = $row['available_colors'] ? explode(',', $row['available_colors']) : [];
                        $color_ids = $row['color_ids'] ? explode(',', $row['color_ids']) : [];
                        $image_urls = $row['image_urls'] ? explode(',', $row['image_urls']) : [];
                        $image_ids = $row['image_ids'] ? explode(',', $row['image_ids']) : [];
                        $variants = isset($product_variants[$row['product_id']]) ? $product_variants[$row['product_id']] : [];
                        $price = $row['price'];
                        $original_price = $row['original_price'];
                        $is_flashsale = isset($flashsale_info[$row['product_id']]);
                        $remaining_quantity = $is_flashsale ? $flashsale_info[$row['product_id']]['max_quantity'] - $flashsale_info[$row['product_id']]['sold_quantity'] : null;
                        $subtotal = $price * $row['quantity'];
                        $total_amount += $subtotal;
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
                <?php
                $discount = $_SESSION['discount'] ?? 0;
                $promo_code = $_SESSION['promo_code'] ?? '';
                if ($discount > 0 && !empty($promo_code)):
                ?>
                    <p style="color: #28a745; font-weight: 500; margin-bottom: 10px;">
                        Bạn đang sử dụng mã giảm giá <strong><?php echo htmlspecialchars($promo_code); ?></strong> và được giảm 
                        <strong><?php echo number_format($discount, 0, ',', '.'); ?> VNĐ</strong>.
                    </p>
                <?php endif; ?>
                <h4 id="total-amount">
                    Tổng Cộng: 
                    <?php 
                    $final_amount = $discount > 0 ? ($_SESSION['discount_type'] === 'percentage' ? $total_amount * (1 - $discount) : $total_amount - $discount) : $total_amount;
                    if ($discount > 0) {
                        echo '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ';
                    }
                    echo number_format($final_amount, 0, ',', '.') . ' VNĐ';
                    ?>
                </h4>
                <a href="orders.php?promo_code=<?php echo urlencode($promo_code); ?>" class="btn btn-primary">Thanh Toán</a>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Giỏ hàng của bạn đang trống. Hãy tiếp tục mua sắm!</p>
                <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        <?php endif; ?>
        <?php
        $html = ob_get_clean();
        $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();
        $cart_count = $cart_count_result->fetch_assoc()['count'];

        echo json_encode([
            'status' => 'success',
            'html' => $html,
            'cart_count' => $cart_count,
            'total_amount_html' => $discount > 0 ? '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ' . number_format($final_amount, 0, ',', '.') . ' VNĐ' : number_format($total_amount, 0, ',', '.') . ' VNĐ'
        ]);
        exit();

    case 'fetch_favorites':
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
            exit();
        }

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
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        ob_start();
        if ($result->num_rows == 0):
        ?>
            <div class="empty-message">
                <p>Chưa có sản phẩm nào trong danh sách yêu thích.</p>
            </div>
        <?php else: ?>
            <table class="favorites-table">
                <tbody id="favorites-items">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-product-id="<?php echo $row['product_id']; ?>">
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
        <?php
        $html = ob_get_clean();
        $favorite_count_sql = "SELECT COUNT(*) as count FROM tbl_favorites WHERE user_id = ?";
        $favorite_count_stmt = $conn->prepare($favorite_count_sql);
        $favorite_count_stmt->bind_param("i", $user_id);
        $favorite_count_stmt->execute();
        $favorite_count_result = $favorite_count_stmt->get_result();
        $favorite_count = $favorite_count_result->fetch_assoc()['count'];

        echo json_encode([
            'status' => 'success',
            'html' => $html,
            'favorite_count' => $favorite_count
        ]);
        exit();

    case 'add_to_cart':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $color = isset($_POST['selected_color']) ? trim($_POST['selected_color']) : null;
        $storage = isset($_POST['selected_storage']) ? trim($_POST['selected_storage']) : null;

        if (!$product_id || !$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thông tin không hợp lệ']);
            exit();
        }

        if (!$color) {
            $color_sql = "SELECT color_name FROM tbl_product_colors WHERE product_id = ? ORDER BY color_id ASC LIMIT 1";
            $color_stmt = $conn->prepare($color_sql);
            $color_stmt->bind_param("i", $product_id);
            $color_stmt->execute();
            $color_result = $color_stmt->get_result();
            $color = $color_result->num_rows > 0 ? $color_result->fetch_assoc()['color_name'] : null;
        }

        if (!$storage) {
            $variant_sql = "SELECT storage FROM tbl_product_variants WHERE product_id = ? ORDER BY variant_id ASC LIMIT 1";
            $variant_stmt = $conn->prepare($variant_sql);
            $variant_stmt->bind_param("i", $product_id);
            $variant_stmt->execute();
            $variant_result = $variant_stmt->get_result();
            $storage = $variant_result->num_rows > 0 ? $variant_result->fetch_assoc()['storage'] : null;
        }

        $flashsale_sql = "
            SELECT f.max_quantity, f.sold_quantity
            FROM tbl_flashsales fs
            JOIN tbl_flashsale f ON fs.flashsale_id = f.flashsale_id
            WHERE f.product_id = ?
            AND fs.status = 'active'
            AND fs.start_time <= NOW()
            AND fs.end_time >= NOW()";
        $flashsale_stmt = $conn->prepare($flashsale_sql);
        $flashsale_stmt->bind_param("i", $product_id);
        $flashsale_stmt->execute();
        $flashsale_result = $flashsale_stmt->get_result();
        if ($flashsale_result->num_rows > 0) {
            $flashsale = $flashsale_result->fetch_assoc();
            $remaining_quantity = $flashsale['max_quantity'] - $flashsale['sold_quantity'];
            if ($quantity > $remaining_quantity) {
                echo json_encode(['status' => 'error', 'message' => "Chỉ còn {$remaining_quantity} sản phẩm trong Flash Sale!"]);
                exit();
            }
        }

        $check_sql = "SELECT cart_id, quantity FROM tbl_cart WHERE user_id = ? AND product_id = ? AND color = ? AND storage = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iiss", $user_id, $product_id, $color, $storage);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $existing = $check_result->fetch_assoc();
            $new_quantity = $existing['quantity'] + $quantity;
            if ($flashsale_result->num_rows > 0 && $new_quantity > $remaining_quantity) {
                echo json_encode(['status' => 'error', 'message' => "Chỉ còn {$remaining_quantity} sản phẩm trong Flash Sale!"]);
                exit();
            }
            $update_sql = "UPDATE tbl_cart SET quantity = ? WHERE cart_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_quantity, $existing['cart_id']);
            $update_stmt->execute();
        } else {
            $insert_sql = "INSERT INTO tbl_cart (user_id, product_id, quantity, color, storage) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiiss", $user_id, $product_id, $quantity, $color, $storage);
            $insert_stmt->execute();
        }

        $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();
        $cart_count = $cart_count_result->fetch_assoc()['count'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
            'cart_count' => $cart_count
        ]);
        exit();

    case 'update_cart':
        $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $color = isset($_POST['color']) ? trim($_POST['color']) : null;
        $storage = isset($_POST['storage']) ? trim($_POST['storage']) : null;

        if ($cart_id <= 0 || $quantity < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Thông tin không hợp lệ']);
            exit();
        }

        $cart_sql = "SELECT product_id FROM tbl_cart WHERE cart_id = ? AND user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("ii", $cart_id, $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        if ($cart_result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Mục giỏ hàng không tồn tại']);
            exit();
        }
        $cart = $cart_result->fetch_assoc();
        $product_id = $cart['product_id'];

        $flashsale_sql = "
            SELECT f.max_quantity, f.sold_quantity
            FROM tbl_flashsales fs
            JOIN tbl_flashsale f ON fs.flashsale_id = f.flashsale_id
            WHERE f.product_id = ?
            AND fs.status = 'active'
            AND fs.start_time <= NOW()
            AND fs.end_time >= NOW()";
        $flashsale_stmt = $conn->prepare($flashsale_sql);
        $flashsale_stmt->bind_param("i", $product_id);
        $flashsale_stmt->execute();
        $flashsale_result = $flashsale_stmt->get_result();
        if ($flashsale_result->num_rows > 0) {
            $flashsale = $flashsale_result->fetch_assoc();
            $remaining_quantity = $flashsale['max_quantity'] - $flashsale['sold_quantity'];
            if ($quantity > $remaining_quantity) {
                echo json_encode(['status' => 'error', 'message' => "Chỉ còn {$remaining_quantity} sản phẩm trong Flash Sale!"]);
                exit();
            }
        }

        $update_sql = "UPDATE tbl_cart SET quantity = ?, color = ?, storage = ? WHERE cart_id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("issii", $quantity, $color, $storage, $cart_id, $user_id);
        $update_stmt->execute();

        $price_sql = "SELECT price FROM tbl_product_variants WHERE product_id = ? AND storage = ?";
        $price_stmt = $conn->prepare($price_sql);
        $price_stmt->bind_param("is", $product_id, $storage);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        $price = $price_result->num_rows > 0 ? $price_result->fetch_assoc()['price'] : 0;

        $subtotal = $price * $quantity;

        $total_sql = "SELECT SUM(pv.price * c.quantity) as total 
                      FROM tbl_cart c 
                      JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage 
                      WHERE c.user_id = ?";
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->bind_param("i", $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total_amount = $total_result->fetch_assoc()['total'] ?? 0;

        $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();
        $cart_count = $cart_count_result->fetch_assoc()['count'];

        $discount = $_SESSION['discount'] ?? 0;
        $discount_type = $_SESSION['discount_type'] ?? '';
        $final_amount = $discount > 0 ? ($discount_type === 'percentage' ? $total_amount * (1 - $discount) : $total_amount - $discount) : $total_amount;
        $total_amount_html = $discount > 0 ? '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ' . number_format($final_amount, 0, ',', '.') . ' VNĐ' : number_format($total_amount, 0, ',', '.') . ' VNĐ';

        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật giỏ hàng thành công!',
            'subtotal' => $subtotal,
            'quantity' => $quantity,
            'cart_count' => $cart_count,
            'total_amount_html' => $total_amount_html
        ]);
        exit();

    case 'remove_cart':
        $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

        if ($cart_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Thông tin không hợp lệ']);
            exit();
        }

        $delete_sql = "DELETE FROM tbl_cart WHERE cart_id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        $delete_stmt->execute();

        $total_sql = "SELECT SUM(pv.price * c.quantity) as total 
                      FROM tbl_cart c 
                      JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage 
                      WHERE c.user_id = ?";
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->bind_param("i", $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total_amount = $total_result->fetch_assoc()['total'] ?? 0;

        $cart_count_sql = "SELECT COUNT(*) as count FROM tbl_cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();
        $cart_count = $cart_count_result->fetch_assoc()['count'];

        $discount = $_SESSION['discount'] ?? 0;
        $discount_type = $_SESSION['discount_type'] ?? '';
        $final_amount = $discount > 0 ? ($discount_type === 'percentage' ? $total_amount * (1 - $discount) : $total_amount - $discount) : $total_amount;
        $total_amount_html = $discount > 0 ? '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ' . number_format($final_amount, 0, ',', '.') . ' VNĐ' : number_format($total_amount, 0, ',', '.') . ' VNĐ';

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
            'cart_count' => $cart_count,
            'total_amount_html' => $total_amount_html
        ]);
        exit();

    case 'apply_promo':
        $promo_code = isset($_POST['promo_code']) ? trim($_POST['promo_code']) : '';
        if (empty($promo_code)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập mã giảm giá']);
            exit();
        }

        $promo_sql = "SELECT * FROM tbl_promotions WHERE promo_code = ? AND status = 'active' AND start_date <= NOW() AND end_date >= NOW()";
        $promo_stmt = $conn->prepare($promo_sql);
        $promo_stmt->bind_param("s", $promo_code);
        $promo_stmt->execute();
        $promo_result = $promo_stmt->get_result();

        if ($promo_result->num_rows > 0) {
            $promo = $promo_result->fetch_assoc();
            $discount = $promo['discount_type'] === 'percentage' ? $promo['discount_value'] / 100 : $promo['discount_value'];
            $_SESSION['promo_code'] = $promo_code;
            $_SESSION['discount'] = $discount;
            $_SESSION['discount_type'] = $promo['discount_type'];

            $total_sql = "SELECT SUM(pv.price * c.quantity) as total 
                          FROM tbl_cart c 
                          JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage 
                          WHERE c.user_id = ?";
            $total_stmt = $conn->prepare($total_sql);
            $total_stmt->bind_param("i", $user_id);
            $total_stmt->execute();
            $total_result = $total_stmt->get_result();
            $total_amount = $total_result->fetch_assoc()['total'] ?? 0;

            $final_amount = $discount > 0 ? ($promo['discount_type'] === 'percentage' ? $total_amount * (1 - $discount) : $total_amount - $discount) : $total_amount;
            $total_amount_html = $discount > 0 ? '<del>' . number_format($total_amount, 0, ',', '.') . ' VNĐ</del> ' . number_format($final_amount, 0, ',', '.') . ' VNĐ' : number_format($total_amount, 0, ',', '.') . ' VNĐ';

            echo json_encode([
                'status' => 'success',
                'message' => "Áp dụng mã {$promo_code} thành công! Giảm " . ($promo['discount_type'] === 'percentage' ? $promo['discount_value'] . '%' : number_format($promo['discount_value'], 0, ',', '.') . ' VNĐ'),
                'total_amount_html' => $total_amount_html,
                'promo_info' => [
                    'promo_code' => $promo_code,
                    'discount' => $promo['discount_value']
                ]
            ]);
        } else {
            unset($_SESSION['promo_code']);
            unset($_SESSION['discount']);
            unset($_SESSION['discount_type']);

            $total_sql = "SELECT SUM(pv.price * c.quantity) as total 
                          FROM tbl_cart c 
                          JOIN tbl_product_variants pv ON c.product_id = pv.product_id AND c.storage = pv.storage 
                          WHERE c.user_id = ?";
            $total_stmt = $conn->prepare($total_sql);
            $total_stmt->bind_param("i", $user_id);
            $total_stmt->execute();
            $total_result = $total_stmt->get_result();
            $total_amount = $total_result->fetch_assoc()['total'] ?? 0;

            echo json_encode([
                'status' => 'error',
                'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.',
                'total_amount_html' => number_format($total_amount, 0, ',', '.') . ' VNĐ'
            ]);
        }
        exit();

    case 'toggle_favorite':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

        if (!$product_id || !$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Thông tin không hợp lệ']);
            exit();
        }

        // Kiểm tra xem sản phẩm đã có trong danh sách yêu thích chưa
        $check_sql = "SELECT favorite_id FROM tbl_favorites WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $is_favorite = $check_result->num_rows > 0;

        if ($is_favorite) {
            // Xóa khỏi danh sách yêu thích
            $delete_sql = "DELETE FROM tbl_favorites WHERE user_id = ? AND product_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $user_id, $product_id);
            $delete_stmt->execute();
            $message = 'Đã xóa sản phẩm khỏi danh sách yêu thích!';
        } else {
            // Thêm vào danh sách yêu thích
            $insert_sql = "INSERT INTO tbl_favorites (user_id, product_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $user_id, $product_id);
            $insert_stmt->execute();
            $message = 'Đã thêm sản phẩm vào danh sách yêu thích!';
        }

        // Đếm số lượng sản phẩm yêu thích
        $favorite_count_sql = "SELECT COUNT(*) as count FROM tbl_favorites WHERE user_id = ?";
        $favorite_count_stmt = $conn->prepare($favorite_count_sql);
        $favorite_count_stmt->bind_param("i", $user_id);
        $favorite_count_stmt->execute();
        $favorite_count_result = $favorite_count_stmt->get_result();
        $favorite_count = $favorite_count_result->fetch_assoc()['count'];

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'is_favorite' => !$is_favorite,
            'favorite_count' => $favorite_count
        ]);
        exit();

    default:
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
        exit();
}

$conn->close();
?>