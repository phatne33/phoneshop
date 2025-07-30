<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đặt múi giờ cho Việt Nam (UTC+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

// Lấy product_id từ URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : die("Không tìm thấy ID sản phẩm.");

// Kiểm tra sản phẩm có trong Flash Sale active
$is_flashsale = false;
$flashsale_data = [];
$flashsale_query = "
    SELECT fs.flashsale_id, fs.title, fs.end_time, 
           f.discount_value, f.discount_type, f.max_quantity, f.sold_quantity
    FROM tbl_flashsales fs
    JOIN tbl_flashsale f ON fs.flashsale_id = f.flashsale_id
    WHERE f.product_id = ? 
    AND fs.status = 'active' 
    AND fs.start_time <= NOW() 
    AND fs.end_time >= NOW()
    LIMIT 1";
$flashsale_stmt = $conn->prepare($flashsale_query);
$flashsale_stmt->bind_param("i", $product_id);
$flashsale_stmt->execute();
$flashsale_result = $flashsale_stmt->get_result();
if ($flashsale_result->num_rows > 0) {
    $is_flashsale = true;
    $flashsale_data = $flashsale_result->fetch_assoc();
}
$flashsale_stmt->close();

// Lấy thông tin sản phẩm
$product_query = "
    SELECT p.*, c.category_name, c.category_id 
    FROM tbl_products p 
    LEFT JOIN tbl_categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ?";
$product_stmt = $conn->prepare($product_query);
if (!$product_stmt) {
    die("Lỗi chuẩn bị truy vấn sản phẩm: " . $conn->error);
}
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
if ($product_result->num_rows == 0) {
    die("Sản phẩm không tồn tại.");
}
$product = $product_result->fetch_assoc();
$product_stmt->close();

// Kiểm tra trạng thái yêu thích
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$product['is_favorite'] = false;
if ($user_id) {
    $check_favorite = $conn->prepare("SELECT COUNT(*) FROM tbl_favorites WHERE user_id = ? AND product_id = ?");
    if (!$check_favorite) {
        die("Lỗi chuẩn bị truy vấn yêu thích: " . $conn->error);
    }
    $check_favorite->bind_param("ii", $user_id, $product_id);
    $check_favorite->execute();
    $product['is_favorite'] = $check_favorite->get_result()->fetch_row()[0] > 0;
    $check_favorite->close();
}

// Lấy thông tin biến thể
$variants_query = "
    SELECT variant_id, ram, storage, price, original_price 
    FROM tbl_product_variants 
    WHERE product_id = ?";
$variants_stmt = $conn->prepare($variants_query);
if (!$variants_stmt) {
    die("Lỗi chuẩn bị truy vấn biến thể: " . $conn->error);
}
$variants_stmt->bind_param("i", $product_id);
$variants_stmt->execute();
$variants_result = $variants_stmt->get_result();
$variants = [];
while ($variant = $variants_result->fetch_assoc()) {
    $variants[] = $variant;
}
$variants_stmt->close();

// Lấy cấu hình chung
$specs_query = "
    SELECT screen_size, refresh_rate, os, chipset, rear_camera, front_camera, battery 
    FROM tbl_product_specifications 
    WHERE product_id = ? 
    LIMIT 1";
$specs_stmt = $conn->prepare($specs_query);
if (!$specs_stmt) {
    die("Lỗi chuẩn bị truy vấn thông số: " . $conn->error);
}
$specs_stmt->bind_param("i", $product_id);
$specs_stmt->execute();
$specs = $specs_stmt->get_result()->fetch_assoc() ?: [];
$specs_stmt->close();

// Lấy thông tin màu sắc và ảnh
$colors_query = "
    SELECT pc.color_id, pc.color_name 
    FROM tbl_product_colors pc 
    WHERE pc.product_id = ?";
$colors_stmt = $conn->prepare($colors_query);
if (!$colors_stmt) {
    die("Lỗi chuẩn bị truy vấn màu sắc: " . $conn->error);
}
$colors_stmt->bind_param("i", $product_id);
$colors_stmt->execute();
$colors_result = $colors_stmt->get_result();
$colors = [];
while ($row = $colors_result->fetch_assoc()) {
    $colors[$row['color_id']] = [
        'color_name' => $row['color_name'],
        'images' => []
    ];
}
$colors_stmt->close();

$images_query = "
    SELECT color_id, image_url 
    FROM tbl_product_images 
    WHERE product_id = ?";
$images_stmt = $conn->prepare($images_query);
if (!$images_stmt) {
    die("Lỗi chuẩn bị truy vấn ảnh: " . $conn->error);
}
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
while ($row = $images_result->fetch_assoc()) {
    $color_id = $row['color_id'] ?? 'no_color';
    if ($color_id === 'no_color' && !isset($colors['no_color'])) {
        $colors['no_color'] = ['color_name' => 'Không xác định', 'images' => []];
    }
    $colors[$color_id]['images'][] = $row['image_url'];
}
$images_stmt->close();

$first_image = !empty($colors) && !empty($colors[array_key_first($colors)]['images'])
    ? $colors[array_key_first($colors)]['images'][0]
    : 'no-image.png';

// Lấy đánh giá (chỉ lấy đánh giá gốc)
$reviews_query = "
    SELECT r.review_id, r.rating, r.comment, r.created_at, r.admin_reply, r.parent_review_id, u.full_name 
    FROM tbl_reviews r 
    JOIN tbl_users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? AND r.parent_review_id IS NULL
    ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
if (!$reviews_stmt) {
    die("Lỗi chuẩn bị truy vấn đánh giá: " . $conn->error);
}
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Lấy các phản hồi
$replies = [];
$replies_query = "
    SELECT r.review_id, r.comment, r.created_at, r.parent_review_id, u.full_name 
    FROM tbl_reviews r 
    JOIN tbl_users u ON r.user_id = u.user_id 
    WHERE r.parent_review_id IS NOT NULL AND r.product_id = ?";
$replies_stmt = $conn->prepare($replies_query);
if (!$replies_stmt) {
    die("Lỗi chuẩn bị truy vấn phản hồi: " . $conn->error);
}
$replies_stmt->bind_param("i", $product_id);
$replies_stmt->execute();
$replies_result = $replies_stmt->get_result();
while ($reply = $replies_result->fetch_assoc()) {
    $replies[$reply['parent_review_id']][] = $reply;
}
$replies_stmt->close();

// Lấy đánh giá trung bình
$avg_rating_query = "
    SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
    FROM tbl_reviews 
    WHERE product_id = ? AND parent_review_id IS NULL";
$avg_rating_stmt = $conn->prepare($avg_rating_query);
if (!$avg_rating_stmt) {
    die("Lỗi chuẩn bị truy vấn đánh giá trung bình: " . $conn->error);
}
$avg_rating_stmt->bind_param("i", $product_id);
$avg_rating_stmt->execute();
$rating_data = $avg_rating_stmt->get_result()->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$review_count = $rating_data['review_count'];
$avg_rating_stmt->close();

// Truy vấn để kiểm tra sản phẩm gợi ý có trong Flash Sale hay không
$flashsale_query = "SELECT fs.product_id, fs.discount_value, fs.discount_type, fs.max_quantity, fs.sold_quantity 
                    FROM tbl_flashsale fs 
                    JOIN tbl_flashsales f ON fs.flashsale_id = f.flashsale_id 
                    WHERE f.status = 'active' AND NOW() BETWEEN f.start_time AND f.end_time";
$flashsale_result = $conn->query($flashsale_query);
$flashsale_data_related = [];
while ($row = $flashsale_result->fetch_assoc()) {
    $flashsale_data_related[$row['product_id']] = [
        'discount_value' => $row['discount_value'],
        'discount_type' => $row['discount_type'],
        'max_quantity' => $row['max_quantity'],
        'sold_quantity' => $row['sold_quantity'],
        'remaining_quantity' => $row['max_quantity'] - $row['sold_quantity']
    ];
}
$flashsale_result->free();

// Lấy danh sách sản phẩm gợi ý
$category_id = $product['category_id'];
$related_products_query = "
    SELECT p.product_id, p.product_name, p.sales_count, 
           MIN(pv.price) as min_price, MIN(pv.original_price) as original_price,
           (SELECT pi.image_url FROM tbl_product_images pi WHERE pi.product_id = p.product_id LIMIT 1) AS default_image,
           (SELECT pi.image_url FROM tbl_product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1 OFFSET 1) AS hover_image,
           (SELECT AVG(r.rating) FROM tbl_reviews r WHERE r.product_id = p.product_id) AS avg_rating,
           (SELECT COUNT(*) FROM tbl_order_details od WHERE od.product_id = p.product_id) AS sold_quantity
    FROM tbl_products p
    LEFT JOIN tbl_product_variants pv ON p.product_id = pv.product_id
    WHERE p.category_id = ? AND p.product_id != ? AND p.status = 'active'
    GROUP BY p.product_id
    ORDER BY p.created_at DESC
    LIMIT 5";
$related_stmt = $conn->prepare($related_products_query);
if (!$related_stmt) {
    die("Lỗi chuẩn bị truy vấn sản phẩm gợi ý: " . $conn->error);
}
$related_stmt->bind_param("ii", $category_id, $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$related_stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm - <?= htmlspecialchars($product['product_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        .product-container1 { width: 100%; border: none; padding: 20px; }
        .product-image-main { width: 100%; max-height: 400px; object-fit: contain; }
        .thumbnail-container { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .thumbnail { width: 60px; height: 60px; object-fit: cover; cursor: pointer; border: 2px solid transparent; }
        .thumbnail.active { border-color: #007bff; }
        .product-title { font-size: 24px; margin-bottom: 10px; font-weight: 700;}
        .product-price { font-size: 20px; color: #e74c3c; font-weight: bold; }
        .original-price { font-size: 16px; color: #999; text-decoration: line-through; margin-right: 5px; }
        .product-stock { font-size: 16px; color: #333; margin-top: 5px; }
        .remaining-quantity { font-size: 16px; color: #e74c3c; margin-top: 5px; }
        .variant-options, .color-options { display: flex; gap: 10px; margin: 10px 0; flex-wrap: wrap; }
        .variant-btn, .color-btn { padding: 10px; border: 1px solid #ddd; cursor: pointer; }
        .variant-btn.active, .color-btn.active { border-color: #007bff; background: #f0f8ff; }
        .buttoncart { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .heart { font-size: 24px; cursor: pointer; border: none; background: none; padding: 5px; transition: color 0.3s ease; }
        .heart.liked { color: red; }
        .heart.unliked { color: grey; }
        .heart:hover { opacity: 0.8; }
        .specifications ul { list-style: none; padding: 0; }
        .specifications li { margin: 5px 0; }
        .rating-stars { direction: rtl; display: flex; justify-content: center; }
        .rating-stars input { display: none; }
        .rating-stars label { font-size: 40px; color: #ccc; cursor: pointer; }
        .rating-stars input:checked ~ label { color: #f1c40f; }
        .rating-stars label:hover, .rating-stars label:hover ~ label { color: #f1c40f; }
        .flashsale-badge { position: absolute; top: 10px; left: 10px; width: 130px; z-index: 10; }
        .related-products-section { margin-top: 30px; }
        .related-products-section h3 { font-size: 24px; margin-bottom: 20px; }
        .related-products-list { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 10px; }
        .related-product { 
            flex: 0 0 350px; 
            display: flex; 
            border: 1px solid #e0e0e0; 
            border-radius: 8px; 
            background: #fff; 
            transition: transform 0.3s ease; 
            text-transform: uppercase; 
            font-family: 'Montserrat', sans-serif; 
        }
        .related-product:hover { border: 1px solid black; }
        .related-product-image { 
            position: relative; 
            width: 150px; 
            height: 150px; 
            overflow: hidden; 
            border-radius: 8px 0 0 8px; 
            background-color: #fff; 
        }
        .related-product-image img { 
            width: 100%; 
            height: 100%; 
            padding: 10px; 
            object-fit: contain; 
            transition: opacity 0.4s ease; 
        }
        .related-product-image .default-image { position: absolute; top: 0; left: 0; }
        .related-product-image .hover-image { position: absolute; top: 0; left: 0; opacity: 0; }
        .related-product:hover .default-image { opacity: 0; }
        .related-product:hover .hover-image { opacity: 1; }
        .related-product-info { 
            flex: 1; 
            padding: 10px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        .related-product-info h5 { 
            font-size: 16px; 
            color: #333; 
            font-weight: 600; 
            margin: 0; 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            height: 42px; 
        }
        .related-product-info .star { font-size: 16px; }
        .related-product-info .star.full { color: #f1c40f; }
        .related-product-info .star.half { color: #f1c40f; }
        .related-product-info .star.empty { color: #ccc; }
        .related-product-info .price-sales { 
            display: flex; 
            flex-direction: column; 
            gap: 5px; 
            font-size: 14px; 
        }
        .related-product-info .price { 
            color: #e74c3c; 
            font-weight: bold; 
            font-size: 15px; 
        }
        .related-product-info .original-price { 
            color: #999; 
            text-decoration: line-through; 
            font-size: 13px; 
        }
        .related-product-info .sales { 
            font-size: 12px; 
            color: #555; 
            font-weight: 600; 
        }
        .related-product-info .remaining { 
            font-size: 12px; 
            color: #e74c3c; 
            font-weight: 600; 
        }
        .related-products-list::-webkit-scrollbar { height: 8px; }
        .rating-form { 
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px; 
            max-width: 600px; 
            margin: 20px auto; 
            padding: 20px; 
            border-radius: 10px; 
        }
        .related-products-list::-webkit-scrollbar-thumb { 
            background: #888; 
            border-radius: 4px; 
        }
        .related-products-list::-webkit-scrollbar-thumb:hover { 
            background: #555; 
        }
        .discount-badge1 { 
            position: absolute; 
            top: -56px; 
            left: -11px; 
            width: 79px !important; 
            z-index: 10; 
        }
        .form-label { 
            display: flex; 
            font-weight: bold; 
            color: #333; 
            font-family: 'Montserrat'; 
            font-size: 16px; 
            justify-content: center; 
        }
        .reply-form { 
            display: none; 
            margin-top: 10px; 
            padding: 10px; 
            background: #f9f9f9; 
            border-radius: 5px; 
        }
        .reply-form textarea { 
            width: 100%; 
            resize: vertical; 
        }
        .reply { 
            margin-left: 20px; 
            border-left: 2px solid #007bff; 
            padding-left: 10px; 
            margin-top: 10px; 
        }
    </style>
</head>
<body>
    <div class="product-container1">
        <div class="row">
            <!-- Ảnh sản phẩm -->
            <div class="col-md-6 position-relative">
                <?php if ($is_flashsale): ?>
                    <img src="public/images/flashsale.webp" alt="Flash Sale" class="flashsale-badge">
                <?php endif; ?>
                <img src="Uploads/<?= htmlspecialchars($first_image) ?>" alt="Ảnh sản phẩm" class="product-image-main" id="main-image">
                <div class="thumbnail-container">
                    <?php foreach ($colors as $color_id => $color_data): ?>
                        <?php foreach ($color_data['images'] as $image): ?>
                            <img src="Uploads/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($color_data['color_name']) ?>" 
                                 class="thumbnail <?= $image === $first_image ? 'active' : '' ?>" 
                                 data-image="Uploads/<?= htmlspecialchars($image) ?>">
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-6">
                <div class="product-info">
                    <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                    <form id="favorite-form" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <button type="button" class="heart <?= $product['is_favorite'] ? 'liked' : 'unliked' ?>" 
                                title="<?= $product['is_favorite'] ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>"
                                onclick="toggleFavorite(<?= $product['product_id'] ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </form>
                    <div class="product-price" id="product-price">
                        <?php if ($is_flashsale && $variants[0]['original_price'] > $variants[0]['price']): ?>
                            <span class="original-price"><?= number_format($variants[0]['original_price'], 0, ',', '.') ?>đ</span>
                            <span><?= number_format($variants[0]['price'], 0, ',', '.') ?>đ</span>
                            <small>(Giảm <?= $flashsale_data['discount_type'] == 'percentage' 
                                ? $flashsale_data['discount_value'] . '%' 
                                : number_format($flashsale_data['discount_value'], 0, ',', '.') . 'đ' ?>)</small>
                        <?php else: ?>
                            <span><?= number_format($variants[0]['price'], 0, ',', '.') ?>đ</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-stock">
                        Số lượng còn trong kho: <?= $product['quantity'] ?>
                    </div>
                    <?php if ($is_flashsale): ?>
                        <div class="remaining-quantity">
                            Số lượng Flash Sale còn lại: <?= $flashsale_data['max_quantity'] - $flashsale_data['sold_quantity'] ?>
                        </div>
                    <?php endif; ?>
                    <div class="product-rating mt-2">
                        <span class="stars">
                            <?php
                            $full_stars = floor($avg_rating);
                            $half_star = ($avg_rating - $full_stars) >= 0.5 ? 1 : 0;
                            $empty_stars = 5 - $full_stars - $half_star;
                            for ($i = 0; $i < $full_stars; $i++) echo '<span class="star full">★</span>';
                            if ($half_star) echo '<span class="star half">★</span>';
                            for ($i = 0; $i < $empty_stars; $i++) echo '<span class="star empty">★</span>';
                            ?>
                        </span>
                        <span class="rating-text" id="rating-text"><?= $avg_rating ?> / 5 (<?= $review_count ?> đánh giá)</span>
                    </div>
                    <form id="cart-form">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <input type="hidden" id="selected_storage" name="storage" value="<?= $variants[0]['storage'] ?? '' ?>">
                        <input type="hidden" id="selected_color" name="color" value="<?= $colors[array_key_first($colors)]['color_name'] ?? '' ?>">
                        <input type="hidden" name="quantity" value="1">
                        <?php if ($is_flashsale): ?>
                            <input type="hidden" name="flashsale_id" value="<?= $flashsale_data['flashsale_id'] ?>">
                        <?php endif; ?>

                        <!-- Chọn cấu hình (RAM và dung lượng) -->
                        <?php if (!empty($variants)): ?>
                            <h5>Chọn cấu hình</h5>
                            <div class="variant-options">
                                <?php foreach ($variants as $index => $variant): ?>
                                    <button type="button" class="variant-btn <?= $index == 0 ? 'active' : '' ?>" 
                                            data-storage="<?= htmlspecialchars($variant['storage']) ?>" 
                                            data-price="<?= $variant['price'] ?>"
                                            data-original-price="<?= $variant['original_price'] ?>"
                                            onclick="selectVariant(this)">
                                        <?= htmlspecialchars($variant['ram']) . " / " . htmlspecialchars($variant['storage']) ?><br>
                                        <?php if ($is_flashsale && $variant['original_price'] > $variant['price']): ?>
                                            <span class="original-price"><?= number_format($variant['original_price'], 0, ',', '.') ?>đ</span>
                                            <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                        <?php else: ?>
                                            <?= number_format($variant['price'], 0, ',', '.') ?>đ
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Chọn màu sắc -->
                        <h5>Chọn màu sắc</h5>
                        <div class="color-options">
                            <?php foreach ($colors as $color_id => $color_data): ?>
                                <button type="button" class="color-btn <?= $color_id == array_key_first($colors) ? 'active' : '' ?>" 
                                        data-color="<?= htmlspecialchars($color_data['color_name']) ?>" 
                                        data-image="<?= !empty($color_data['images']) ? 'Uploads/' . htmlspecialchars($color_data['images'][0]) : '' ?>"
                                        onclick="selectColor(this)">
                                    <?= htmlspecialchars($color_data['color_name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="buttoncart" id="add-to-cart-btn" onclick="addToCart()">Thêm vào giỏ hàng</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mô tả và thông số kỹ thuật -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="description-box">
                    <h5>Mô tả sản phẩm</h5>
                    <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Không có mô tả.')) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="specifications" style="width: 100%;">
                    <h3>Cấu hình sản phẩm</h3>
                    <?php if (!empty($specs)): ?>
                        <ul>
                            <?php if ($specs['screen_size']) echo "<li><strong>Kích thước màn hình:</strong> " . htmlspecialchars($specs['screen_size']) . "</li>"; ?>
                            <?php if ($specs['refresh_rate']) echo "<li><strong>Tần số quét:</strong> " . htmlspecialchars($specs['refresh_rate']) . "</li>"; ?>
                            <?php if ($specs['os']) echo "<li><strong>Hệ điều hành:</strong> " . htmlspecialchars($specs['os']) . "</li>"; ?>
                            <?php if ($specs['chipset']) echo "<li><strong>Chipset:</strong> " . htmlspecialchars($specs['chipset']) . "</li>"; ?>
                            <?php if ($specs['rear_camera']) echo "<li><strong>Camera sau:</strong> " . htmlspecialchars($specs['rear_camera']) . "</li>"; ?>
                            <?php if ($specs['front_camera']) echo "<li><strong>Camera trước:</strong> " . htmlspecialchars($specs['front_camera']) . "</li>"; ?>
                            <?php if ($specs['battery']) echo "<li><strong>Pin:</strong> " . htmlspecialchars($specs['battery']) . "</li>"; ?>
                        </ul>
                    <?php else: ?>
                        <p>Không có thông tin cấu hình.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Đánh giá sản phẩm -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="reviews-section">
                    <h3 class="mb-3">Đánh giá sản phẩm</h3>
                    <h4 class="mt-4">Gửi đánh giá của bạn</h4>
                    <form id="rating-form" class="rating-form">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <div class="mb-3">
                            <label for="comment" class="form-label">Chia sẻ trải nghiệm của bạn với sản phẩm</label>
                            <div class="rating-stars">
                                <input type="radio" name="rating" id="star5" value="5" required><label for="star5">★</label>
                                <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                                <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                                <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                                <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                            </div>
                            <textarea name="comment" id="comment" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" style="width: 100%;" onclick="submitReview()">Gửi đánh giá</button>
                    </form>

                    <?php if ($reviews_result->num_rows > 0): ?>
                        <div class="reviews-list mt-4" id="reviews-list">
                            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                <div class="review mb-3 p-3 border rounded" id="review-<?= $review['review_id'] ?>">
                                    <p class="mb-1"><strong><?= htmlspecialchars($review['full_name']) ?></strong> - <?= $review['created_at'] ?></p>
                                    <p class="mb-1">Đánh giá: <?= str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']) ?></p>
                                    <p><?= htmlspecialchars($review['comment']) ?></p>
                                    <?php if (!empty($review['admin_reply'])): ?>
                                        <div class="admin-reply mt-2 p-2 bg-light border rounded">
                                            <strong>Phản hồi từ Admin:</strong>
                                            <p class="mb-0">"<?= htmlspecialchars($review['admin_reply']) ?>"</p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($user_id): ?>
                                        <button class="btn btn-link btn-sm mt-1 reply-btn" onclick="toggleReplyForm(<?= $review['review_id'] ?>)">Trả lời</button>
                                        <form id="reply-form-<?= $review['review_id'] ?>" class="reply-form" onsubmit="submitReply(<?= $review['review_id'] ?>); return false;">
                                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                            <input type="hidden" name="parent_review_id" value="<?= $review['review_id'] ?>">
                                            <div class="mb-3">
                                                <textarea name="comment" class="form-control" rows="3" required placeholder="Nhập phản hồi của bạn..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Gửi phản hồi</button>
                                            <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="toggleReplyForm(<?= $review['review_id'] ?>)">Hủy</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (isset($replies[$review['review_id']])): ?>
                                        <?php foreach ($replies[$review['review_id']] as $reply): ?>
                                            <div class="reply mt-2">
                                                <p class="mb-1"><strong><?= htmlspecialchars($reply['full_name']) ?></strong> - <?= $reply['created_at'] ?></p>
                                                <p><?= htmlspecialchars($reply['comment']) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mt-3" id="no-reviews">Chưa có đánh giá nào cho sản phẩm này.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Phần Có thể bạn sẽ thích -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="related-products-section">
                    <h3>Có thể bạn sẽ thích</h3>
                    <?php if (!empty($related_products)): ?>
                        <div class="related-products-list">
                            <?php foreach ($related_products as $related): ?>
                                <?php
                                $related_product_id = $related['product_id'];
                                $related_avg_rating = $related['avg_rating'] ? round($related['avg_rating'], 1) : 0;

                                // Kiểm tra sản phẩm có trong Flash Sale không
                                $is_flashsale_related = isset($flashsale_data_related[$related_product_id]);
                                $original_price = $related['original_price'];
                                $min_price = $related['min_price'];
                                if ($is_flashsale_related) {
                                    $discount_value = $flashsale_data_related[$related_product_id]['discount_value'];
                                    $discount_type = $flashsale_data_related[$related_product_id]['discount_type'];
                                    $remaining_quantity = $flashsale_data_related[$related_product_id]['remaining_quantity'];
                                    if ($discount_type == 'percentage') {
                                        $original_price = $min_price / (1 - ($discount_value / 100));
                                    } else {
                                        $original_price = $min_price + $discount_value;
                                    }
                                }
                                ?>
                                <div class="related-product">
                                    <a href="index.php?product_id=<?= $related['product_id'] ?>" class="product-link">
                                        <div class="related-product-image">
                                            <img src="Uploads/<?= htmlspecialchars($related['default_image'] ?: 'no-image.png') ?>" alt="<?= htmlspecialchars($related['product_name']) ?>" class="default-image">
                                            <img src="Uploads/<?= htmlspecialchars($related['hover_image'] ?: $related['default_image'] ?: 'no-image.png') ?>" alt="<?= htmlspecialchars($related['product_name']) ?>" class="hover-image">
                                            <?php if ($is_flashsale_related): ?>
                                                <img src="public/images/flashsale.webp" alt="Flash Sale" class="discount-badge1">
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    <div class="related-product-info">
                                        <a href="index.php?product_id=<?= $related['product_id'] ?>" class="product-link">
                                            <h5><?= htmlspecialchars($related['product_name']) ?></h5>
                                            <div class="price-sales">
                                                <p class="price">
                                                    <?php if ($is_flashsale_related): ?>
                                                        <span class="original-price"><?= number_format($original_price, 0, ',', '.') ?> VNĐ</span>
                                                    <?php endif; ?>
                                                    <?= number_format($related['min_price'], 0, ',', '.') ?> VNĐ
                                                </p>
                                                <span class="sales">Đã bán: <?= $related['sold_quantity'] ?: 0 ?></span>
                                                <?php if ($is_flashsale_related): ?>
                                                    <span class="remaining">Còn lại: <?= $remaining_quantity ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Không có sản phẩm gợi ý nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Quay lại danh sách sản phẩm</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        let reviewCount = <?= $review_count ?>;
let avgRating = <?= $avg_rating ?>;
const isFlashsale = <?= $is_flashsale ? 'true' : 'false' ?>;
const flashsaleId = <?= $is_flashsale ? $flashsale_data['flashsale_id'] : 'null' ?>;
const maxQuantity = <?= $is_flashsale ? $flashsale_data['max_quantity'] : 'null' ?>;
const soldQuantity = <?= $is_flashsale ? $flashsale_data['sold_quantity'] : 'null' ?>;

function selectVariant(button) {
    document.getElementById("selected_storage").value = button.getAttribute("data-storage");
    document.querySelectorAll(".variant-btn").forEach(btn => btn.classList.remove("active"));
    button.classList.add("active");
    const price = button.getAttribute("data-price");
    const originalPrice = button.getAttribute("data-original-price");
    const discountValue = <?= $is_flashsale ? $flashsale_data['discount_value'] : '0' ?>;
    const discountType = '<?= $is_flashsale ? $flashsale_data['discount_type'] : '' ?>';
    let discountText = '';
    if (discountType === 'percentage') {
        discountText = `(Giảm ${discountValue}%)`;
    } else if (discountType === 'fixed') {
        discountText = `(Giảm ${parseInt(discountValue).toLocaleString('vi-VN')}đ)`;
    }
    if (isFlashsale && originalPrice > price) {
        document.getElementById("product-price").innerHTML = `
            <span class="original-price">${parseInt(originalPrice).toLocaleString('vi-VN')}đ</span>
            <span>${parseInt(price).toLocaleString('vi-VN')}đ</span>
            <small>${discountText}</small>`;
    } else {
        document.getElementById("product-price").innerHTML = `<span>${parseInt(price).toLocaleString('vi-VN')}đ</span>`;
    }
}

function selectColor(button) {
    document.getElementById("selected_color").value = button.getAttribute("data-color");
    document.querySelectorAll(".color-btn").forEach(btn => btn.classList.remove("active"));
    button.classList.add("active");
    const image = button.getAttribute("data-image");
    if (image) {
        document.getElementById("main-image").setAttribute("src", image);
        document.querySelectorAll(".thumbnail").forEach(thumb => thumb.classList.remove("active"));
        document.querySelector(`.thumbnail[data-image="${image}"]`)?.classList.add("active");
    }
}

document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.addEventListener('click', function() {
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove("active"));
        this.classList.add("active");
        document.getElementById("main-image").setAttribute("src", this.getAttribute("data-image"));
    });
});

function toggleReplyForm(reviewId) {
    const form = document.getElementById(`reply-form-${reviewId}`);
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}

async function toggleFavorite(productId) {
    const formData = new FormData();
    formData.append('action', 'toggle_favorite');
    formData.append('product_id', productId);

    try {
        const response = await fetch('views/ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            const heartBtn = document.querySelector('.heart');
            if (data.is_favorite) {
                heartBtn.classList.remove('unliked');
                heartBtn.classList.add('liked');
                heartBtn.title = 'Xóa khỏi yêu thích';
            } else {
                heartBtn.classList.remove('liked');
                heartBtn.classList.add('unliked');
                heartBtn.title = 'Thêm vào yêu thích';
            }
            // Gửi sự kiện cập nhật số lượng yêu thích tới cửa sổ chính
            window.parent.postMessage({
                type: 'updateFavoriteCount',
                count: data.is_favorite ? parseInt(document.getElementById('favorite-count')?.textContent || 0) + 1 : parseInt(document.getElementById('favorite-count')?.textContent || 0) - 1
            }, '*');
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Đã xảy ra lỗi khi xử lý yêu cầu.',
            timer: 1000,
            showConfirmButton: false
        });
    }
}

async function addToCart() {
    const form = document.getElementById('cart-form');
    const formData = new FormData(form);
    formData.append('action', 'add_to_cart');

    if (isFlashsale) {
        const quantity = parseInt(formData.get('quantity'));
        const remainingQuantity = maxQuantity - soldQuantity;
        if (quantity > remainingQuantity) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Chỉ còn ${remainingQuantity} sản phẩm trong Flash Sale!`,
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }
    }

    try {
        const response = await fetch('views/ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            // Gửi sự kiện cập nhật số lượng giỏ hàng tới cửa sổ chính
            window.parent.postMessage({
                type: 'updateCartCount',
                count: parseInt(document.getElementById('cart-count')?.textContent || 0) + 1
            }, '*');
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Đã xảy ra lỗi khi xử lý yêu cầu.',
            timer: 1000,
            showConfirmButton: false
        });
    }
}

async function submitReview() {
    const form = document.getElementById('rating-form');
    const formData = new FormData(form);
    formData.append('action', 'submit_review');

    const rating = form.querySelector('input[name="rating"]:checked');
    const comment = form.querySelector('#comment').value.trim();

    if (!rating) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Vui lòng chọn số sao đánh giá.',
            timer: 1000,
            showConfirmButton: false
        });
        return;
    }

    if (!comment) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Bình luận không được để trống.',
            timer: 1000,
            showConfirmButton: false
        });
        return;
    }

    try {
        const response = await fetch('views/ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });

            const reviewsList = document.getElementById('reviews-list');
            const noReviews = document.getElementById('no-reviews');

            if (noReviews) {
                noReviews.remove();
                const newList = document.createElement('div');
                newList.className = 'reviews-list mt-4';
                newList.id = 'reviews-list';
                document.querySelector('.reviews-section').appendChild(newList);
            }

            const newReview = document.createElement('div');
            newReview.className = 'review mb-3 p-3 border rounded';
            newReview.id = `review-${data.review.review_id}`;
            newReview.innerHTML = `
                <p class="mb-1"><strong>${data.review.full_name}</strong> - ${data.review.created_at}</p>
                <p class="mb-1">Đánh giá: ${'★'.repeat(data.review.rating)}${'☆'.repeat(5 - data.review.rating)}</p>
                <p>${data.review.comment}</p>
                <?php if ($user_id): ?>
                    <button class="btn btn-link btn-sm mt-1 reply-btn" onclick="toggleReplyForm(${data.review.review_id})">Trả lời</button>
                    <form id="reply-form-${data.review.review_id}" class="reply-form" onsubmit="submitReply(${data.review.review_id}); return false;">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <input type="hidden" name="parent_review_id" value="${data.review.review_id}">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" required placeholder="Nhập phản hồi của bạn..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Gửi phản hồi</button>
                        <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="toggleReplyForm(${data.review.review_id})">Hủy</button>
                    </form>
                <?php endif; ?>
            `;
            reviewsList.insertBefore(newReview, reviewsList.firstChild);

            reviewCount++;
            avgRating = ((avgRating * (reviewCount - 1)) + parseInt(data.review.rating)) / reviewCount;
            document.getElementById('rating-text').textContent = `${avgRating.toFixed(1)} / 5 (${reviewCount} đánh giá)`;

            const starsContainer = document.querySelector('.stars');
            starsContainer.innerHTML = '';
            const fullStars = Math.floor(avgRating);
            const halfStar = (avgRating - fullStars) >= 0.5 ? 1 : 0;
            const emptyStars = 5 - fullStars - halfStar;
            for (let i = 0; i < fullStars; i++) {
                starsContainer.innerHTML += '<span class="star full">★</span>';
            }
            if (halfStar) {
                starsContainer.innerHTML += '<span class="star half">★</span>';
            }
            for (let i = 0; i < emptyStars; i++) {
                starsContainer.innerHTML += '<span class="star empty">★</span>';
            }

            form.reset();
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        }
    } catch (error) {
        console.error('Lỗi:', error);
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Đã xảy ra lỗi khi xử lý yêu cầu: ' + error.message,
            timer: 1500,
            showConfirmButton: false
        });
    }
}

async function submitReply(reviewId) {
    const form = document.getElementById(`reply-form-${reviewId}`);
    const formData = new FormData(form);
    formData.append('action', 'submit_reply');

    const comment = form.querySelector('textarea[name="comment"]').value.trim();

    if (!comment) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Phản hồi không được để trống.',
            timer: 1000,
            showConfirmButton: false
        });
        return;
    }

    try {
        const response = await fetch('views/ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: data.message,
                timer: 1000,
                showConfirmButton: false
            });

            const reviewDiv = document.getElementById(`review-${reviewId}`);
            const replyDiv = document.createElement('div');
            replyDiv.className = 'reply mt-2';
            replyDiv.innerHTML = `
                <p class="mb-1"><strong>${data.reply.full_name}</strong> - ${data.reply.created_at}</p>
                <p>${data.reply.comment}</p>
            `;
            reviewDiv.insertBefore(replyDiv, form);

            form.style.display = 'none';
            form.reset();
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        }
    } catch (error) {
        console.error('Lỗi:', error);
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Đã xảy ra lỗi khi xử lý yêu cầu: ' + error.message,
            timer: 1500,
            showConfirmButton: false
        });
    }
}
    </script>
</body>
</html>