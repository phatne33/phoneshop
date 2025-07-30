<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối database
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra trạng thái yêu thích cho từng sản phẩm
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$favorite_status = [];
if ($user_id) {
    $favorite_query = "SELECT product_id FROM tbl_favorites WHERE user_id = ?";
    $favorite_stmt = $conn->prepare($favorite_query);
    $favorite_stmt->bind_param("i", $user_id);
    $favorite_stmt->execute();
    $favorite_result = $favorite_stmt->get_result();
    while ($row = $favorite_result->fetch_assoc()) {
        $favorite_status[$row['product_id']] = true;
    }
    $favorite_stmt->close();
}

// Truy vấn để kiểm tra sản phẩm có trong Flash Sale hay không
$flashsale_query = "SELECT fs.product_id, fs.discount_value, fs.discount_type, fs.max_quantity, fs.sold_quantity 
                    FROM tbl_flashsale fs 
                    JOIN tbl_flashsales f ON fs.flashsale_id = f.flashsale_id 
                    WHERE f.status = 'active' AND NOW() BETWEEN f.start_time AND f.end_time";
$flashsale_result = $conn->query($flashsale_query);
$flashsale_data = [];
while ($row = $flashsale_result->fetch_assoc()) {
    $flashsale_data[$row['product_id']] = [
        'discount_value' => $row['discount_value'],
        'discount_type' => $row['discount_type'],
        'max_quantity' => $row['max_quantity'],
        'sold_quantity' => $row['sold_quantity'],
        'remaining_quantity' => $row['max_quantity'] - $row['sold_quantity']
    ];
}
$flashsale_result->free();

// Xử lý tìm kiếm và lọc
$conditions = [];
$params = [];
$types = "";

// Tìm kiếm theo tên sản phẩm
if (!empty($_GET['search'])) {
    $conditions[] = "p.product_name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= "s";
}

// Lọc theo hãng (category_id)
if (!empty($_GET['category_id'])) {
    $conditions[] = "p.category_id = ?";
    $params[] = $_GET['category_id'];
    $types .= "i";
}

// Lọc theo hệ điều hành
if (!empty($_GET['os'])) {
    $conditions[] = "ps.os LIKE ?";
    $params[] = "%" . $_GET['os'] . "%";
    $types .= "s";
}

// Lọc theo RAM (dùng tbl_product_variants)
if (!empty($_GET['ram'])) {
    $conditions[] = "pv.ram = ?";
    $params[] = $_GET['ram'];
    $types .= "s";
}

// Lọc theo bộ nhớ trong (dùng tbl_product_variants)
if (!empty($_GET['storage'])) {
    $conditions[] = "pv.storage = ?";
    $params[] = $_GET['storage'];
    $types .= "s";
}

// Lọc theo kích thước màn hình
if (!empty($_GET['screen_size'])) {
    $conditions[] = "ps.screen_size = ?";
    $params[] = $_GET['screen_size'];
    $types .= "s";
}

// Lọc theo tần số quét
if (!empty($_GET['refresh_rate'])) {
    $conditions[] = "ps.refresh_rate = ?";
    $params[] = $_GET['refresh_rate'];
    $types .= "s";
}

// Lọc theo giá (dùng giá từ tbl_product_variants)
if (!empty($_GET['price_min'])) {
    $conditions[] = "pv.price >= ?";
    $params[] = $_GET['price_min'];
    $types .= "d";
}
if (!empty($_GET['price_max'])) {
    $conditions[] = "pv.price <= ?";
    $params[] = $_GET['price_max'];
    $types .= "d";
}

// Phân trang
$limit = 10;
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số sản phẩm
$count_query = "SELECT COUNT(DISTINCT p.product_id) as total 
                FROM tbl_products p
                LEFT JOIN tbl_product_specifications ps ON p.product_id = ps

.product_id
                LEFT JOIN tbl_product_variants pv ON p.product_id = pv.product_id
                WHERE p.status = 'active'";
if (!empty($conditions)) {
    $count_query .= " AND " . implode(" AND ", $conditions);
}
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);
$count_stmt->close();

// Xây dựng truy vấn chính với phân trang
$query = "SELECT DISTINCT p.product_id, p.product_name, p.sales_count, 
          MIN(pv.price) as min_price, MIN(pv.original_price) as original_price
          FROM tbl_products p
          LEFT JOIN tbl_product_specifications ps ON p.product_id = ps.product_id
          LEFT JOIN tbl_product_variants pv ON p.product_id = pv.product_id
          WHERE p.status = 'active'";
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}
$query .= " GROUP BY p.product_id, p.product_name, p.sales_count";
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Truy vấn sản phẩm bán chạy
$best_sellers_query = "SELECT DISTINCT p.product_id, p.product_name, p.sales_count, 
                       MIN(pv.price) as min_price, MIN(pv.original_price) as original_price
                       FROM tbl_products p 
                       LEFT JOIN tbl_product_variants pv ON p.product_id = pv.product_id
                       WHERE p.status = 'active'
                       GROUP BY p.product_id, p.product_name, p.sales_count
                       ORDER BY p.sales_count DESC 
                       LIMIT 5";
$best_sellers_result = $conn->query($best_sellers_query);

// Truy vấn sản phẩm đánh giá cao
$top_rated_query = "SELECT DISTINCT p.product_id, p.product_name, p.sales_count, 
                    MIN(pv.price) as min_price, MIN(pv.original_price) as original_price, 
                    AVG(r.rating) as avg_rating
                    FROM tbl_products p 
                    LEFT JOIN tbl_product_variants pv ON p.product_id = pv.product_id
                    LEFT JOIN tbl_reviews r ON p.product_id = r.product_id
                    WHERE p.status = 'active'
                    GROUP BY p.product_id, p.product_name, p.sales_count
                    ORDER BY avg_rating DESC 
                    LIMIT 5";
$top_rated_result = $conn->query($top_rated_query);

// Lấy tên danh mục nếu có category_id
$category_name = "TẤT CẢ SẢN PHẨM";
if (!empty($_GET['category_id'])) {
    $cat_stmt = $conn->prepare("SELECT category_name FROM tbl_categories WHERE category_id = ?");
    $cat_stmt->bind_param("i", $_GET['category_id']);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat_row = $cat_result->fetch_assoc()) {
        $category_name = "Điện thoại " . $cat_row['category_name'];
    }
    $cat_stmt->close();
}

if (isset($_GET['product_id'])) {
    include 'views/sanpham.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'addresses' && !empty($user_id)) {
    include 'addresses.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'myorders' && !empty($user_id)) {
    include 'myorders.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'account' && !empty($user_id)) {
    include 'account.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'cart' && !empty($user_id)) {
    include 'cart.php';
}
elseif (isset($_GET['page']) && $_GET['page'] === 'favorites' && !empty($user_id)) {
    include 'favorites.php';
}else {
?>

<?php  
include 'flashsale.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhoneStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <div class="filter-sidebar" data-aos="fade-up">
            <div class="filter-header">
                <h3>BỘ LỌC TÌM KIẾM</h3>
                <button type="button" class="toggle-filter-btn" onclick="toggleFilter()">   
                    <span class="toggle-text">THU GỌN</span>
                    <span class="toggle-icon">▼</span>
                </button>
            </div>
            <div class="filter-content collapsed">
                <form method="GET" action="index.php" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="filter-group">
                        <h4>MỨC GIÁ</h4>
                        <div class="price-range">
                            <input type="number" name="price_min" placeholder="Từ" value="<?php echo isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : ''; ?>" min="0" step="1000000">
                            <input type="number" name="price_max" placeholder="Đến" value="<?php echo isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : ''; ?>" min="0" step="1000000">
                        </div>
                    </div>
                    <div class="filter-group">
                        <h4>HÃNG</h4>
                        <select name="category_id">
                            <option value="">Tất cả</option>
                            <?php
                            $category_query = "SELECT * FROM tbl_categories";
                            $category_result = $conn->query($category_query);
                            while ($category = $category_result->fetch_assoc()) {
                                $selected = (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'selected' : '';
                                echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
                            }
                            $category_result->free();
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <h4>HỆ ĐIỀU HÀNH</h4>
                        <label><input type="checkbox" name="os" value="iOS" <?php echo (isset($_GET['os']) && $_GET['os'] == 'iOS') ? 'checked' : ''; ?>> iOS</label>
                        <label><input type="checkbox" name="os" value="Android" <?php echo (isset($_GET['os']) && $_GET['os'] == 'Android') ? 'checked' : ''; ?>> Android</label>
                    </div>
                    <div class="filter-group">
                        <h4>DUNG LƯỢNG ROM</h4>
                        <label><input type="checkbox" name="storage" value="128GB" <?php echo (isset($_GET['storage']) && $_GET['storage'] == '128GB') ? 'checked' : ''; ?>> 128GB</label>
                        <label><input type="checkbox" name="storage" value="256GB" <?php echo (isset($_GET['storage']) && $_GET['storage'] == '256GB') ? 'checked' : ''; ?>> 256GB</label>
                        <label><input type="checkbox" name="storage" value="512GB" <?php echo (isset($_GET['storage']) && $_GET['storage'] == '512GB') ? 'checked' : ''; ?>> 512GB</label>
                    </div>
                    <div class="filter-group">
                        <h4>RAM</h4>
                        <label><input type="checkbox" name="ram" value="8GB" <?php echo (isset($_GET['ram']) && $_GET['ram'] == '8GB') ? 'checked' : ''; ?>> 8GB</label>
                        <label><input type="checkbox" name="ram" value="12GB" <?php echo (isset($_GET['ram']) && $_GET['ram'] == '12GB') ? 'checked' : ''; ?>> 12GB</label>
                    </div>
                    <div class="filter-group">
                        <h4>KÍCH THƯỚC MÀN HÌNH</h4>
                        <label><input type="checkbox" name="screen_size" value="6.3" <?php echo (isset($_GET['screen_size']) && $_GET['screen_size'] == '6.3') ? 'checked' : ''; ?>> 6.3 inches</label>
                        <label><input type="checkbox" name="screen_size" value="6.9" <?php echo (isset($_GET['screen_size']) && $_GET['screen_size'] == '6.9') ? 'checked' : ''; ?>> 6.9 inches</label>
                    </div>
                    <div class="filter-group">
                        <h4>TẦN SỐ QUÉT</h4>
                        <label><input type="checkbox" name="refresh_rate" value="120Hz" <?php echo (isset($_GET['refresh_rate']) && $_GET['refresh_rate'] == '120Hz') ? 'checked' : ''; ?>> 120Hz</label>
                    </div>
                    <button type="submit" class="filter-btn">LỌC</button>
                </form>
            </div>
        </div>

        <div class="product-main" data-aos="fade-up">
            <div class="product-container">
                <div class="category-header">
                    <h2><?php echo htmlspecialchars($category_name); ?></h2>
                    <div class="category-list">
                        <?php
                        $category_query = "SELECT * FROM tbl_categories";
                        $category_result = $conn->query($category_query);
                        while ($category = $category_result->fetch_assoc()) {
                            $active = (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'active' : '';
                            echo '<div class="category-item">';
                            echo '<a href="index.php?category_id=' . $category['category_id'] . '" class="category-link ' . $active . '">' . htmlspecialchars($category['category_name']) . '</a>';
                            echo '</div>';
                        }
                        $category_result->free();
                        ?>
                    </div>
                </div>

                <div class="product-list">
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        $product_id = $row['product_id'];
                        $is_favorite = isset($favorite_status[$product_id]) && $favorite_status[$product_id];

                        // Lấy ảnh đầu tiên và ảnh thứ hai từ tbl_product_images
                        $image_query = "SELECT image_url 
                                        FROM tbl_product_images 
                                        WHERE product_id = ? 
                                        ORDER BY image_id ASC 
                                        LIMIT 2";
                        $image_stmt = $conn->prepare($image_query);
                        $image_stmt->bind_param("i", $product_id);
                        $image_stmt->execute();
                        $image_result = $image_stmt->get_result();
                        $images = $image_result->fetch_all(MYSQLI_ASSOC);
                        $image_stmt->close();

                        $default_image = $images[0]['image_url'] ?? 'Uploads/no-image.png';
                        $hover_image = $images[1]['image_url'] ?? $default_image;

                        // Lấy đánh giá trung bình
                        $avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                                            FROM tbl_reviews 
                                            WHERE product_id = ?";
                        $avg_rating_stmt = $conn->prepare($avg_rating_query);
                        $avg_rating_stmt->bind_param("i", $product_id);
                        $avg_rating_stmt->execute();
                        $avg_rating_result = $avg_rating_stmt->get_result();
                        $rating_data = $avg_rating_result->fetch_assoc();
                        $avg_rating_stmt->close();

                        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
                        $review_count = $rating_data['review_count'];

                        // Lấy variants từ tbl_product_variants
                        $variant_query = "SELECT ram, storage FROM tbl_product_variants WHERE product_id = ?";
                        $variant_stmt = $conn->prepare($variant_query);
                        $variant_stmt->bind_param("i", $product_id);
                        $variant_stmt->execute();
                        $variant_result = $variant_stmt->get_result();
                        $variants = [];
                        while ($variant = $variant_result->fetch_assoc()) {
                            $variants[] = $variant['ram'] . " / " . $variant['storage'];
                        }
                        $variant_stmt->close();

                        // Kiểm tra sản phẩm có trong Flash Sale không
                        $is_flashsale = isset($flashsale_data[$product_id]);
                        $original_price = $row['original_price'];
                        $min_price = $row['min_price'];
                        if ($is_flashsale) {
                            $discount_value = $flashsale_data[$product_id]['discount_value'];
                            $discount_type = $flashsale_data[$product_id]['discount_type'];
                            $remaining_quantity = $flashsale_data[$product_id]['remaining_quantity'];
                            if ($discount_type == 'percentage') {
                                $original_price = $min_price / (1 - ($discount_value / 100));
                            } else {
                                $original_price = $min_price + $discount_value;
                            }
                        }

                        echo '<div class="product">';
                        echo '<a href="index.php?product_id=' . $product_id . '" class="product-link">';
                        echo '<div class="product-image">';
                    echo '<img src="Uploads/' . htmlspecialchars($default_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="default-image">';
                    echo '<img src="Uploads/' . htmlspecialchars($hover_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="hover-image">';
                    if ($is_flashsale) {
                        echo '<img src="public/images/flashsale.webp" alt="Flash Sale"class="discount-badge" style="    width: 50px;
    height: 50px;
    top: -10px;"">';
                    }
                    echo '</div>';
                        echo '<h3>' . htmlspecialchars($row['product_name']) . ' <span class="rating">(' . number_format($avg_rating, 1) . '<i class="fa-solid fa-star"></i>)</span></h3>';
                        
                        echo '<div class="price-sales1">';
                        echo '<p class="price1">';
                        if ($is_flashsale) {
                            echo '<span class="original-price">' . number_format($original_price, 0, ',', '.') . '</span> ';
                        }
                        echo number_format($row['min_price'], 0, ',', '.') . '</p>';
                        echo '<div class="price-details1" style="justify-content: space-between; display: flex;">';
                        if ($is_flashsale) {
                        }
                        echo '<span class="sales">Đã bán: '.$row['sales_count'] . '</span>';
                        if ($is_flashsale) {
                            echo '<span class="remaining">Còn lại: ' . $remaining_quantity . '</span>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
                        echo '</div>';
                    }
                    $result->free();
                    ?>
                </div>
                <div class="pagination">
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo '<a href="index.php?' . http_build_query(array_merge($_GET, ['page_num' => $i])) . '" class="page-link ' . $active . '">' . $i . '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm bán chạy -->
    <div class="best-sellers-container" data-aos="fade-up">
        <h2>SẢN PHẨM BÁN CHẠY</h2>
        <div class="slider-wrapper">
            <button class="slider-btn prev" onclick="slide('best-sellers', -1)">❮</button>
            <div class="horizontal-scroll" id="best-sellers">
                <?php
                while ($row = $best_sellers_result->fetch_assoc()) {
                    $product_id = $row['product_id'];
                    $is_favorite = isset($favorite_status[$product_id]) && $favorite_status[$product_id];

                    // Lấy ảnh đầu tiên và ảnh thứ hai từ tbl_product_images
                    $image_query = "SELECT image_url 
                                    FROM tbl_product_images 
                                    WHERE product_id = ? 
                                    ORDER BY image_id ASC 
                                    LIMIT 2";
                    $image_stmt = $conn->prepare($image_query);
                    $image_stmt->bind_param("i", $product_id);
                    $image_stmt->execute();
                    $image_result = $image_stmt->get_result();
                    $images = $image_result->fetch_all(MYSQLI_ASSOC);
                    $image_stmt->close();

                    $default_image = $images[0]['image_url'] ?? 'Uploads/no-image.png';
                    $hover_image = $images[1]['image_url'] ?? $default_image;

                    // Lấy đánh giá trung bình
                    $avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                                        FROM tbl_reviews 
                                        WHERE product_id = ?";
                    $avg_rating_stmt = $conn->prepare($avg_rating_query);
                    $avg_rating_stmt->bind_param("i", $product_id);
                    $avg_rating_stmt->execute();
                    $avg_rating_result = $avg_rating_stmt->get_result();
                    $rating_data = $avg_rating_result->fetch_assoc();
                    $avg_rating_stmt->close();

                    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
                    $review_count = $rating_data['review_count'];

                    // Lấy variants từ tbl_product_variants
                    $variant_query = "SELECT ram, storage FROM tbl_product_variants WHERE product_id = ?";
                    $variant_stmt = $conn->prepare($variant_query);
                    $variant_stmt->bind_param("i", $product_id);
                    $variant_stmt->execute();
                    $variant_result = $variant_stmt->get_result();
                    $variants = [];
                    while ($variant = $variant_result->fetch_assoc()) {
                        $variants[] = $variant['ram'] . " / " . $variant['storage'];
                    }
                    $variant_stmt->close();

                    // Kiểm tra sản phẩm có trong Flash Sale không
                    $is_flashsale = isset($flashsale_data[$product_id]);
                    $original_price = $row['original_price'];
                    $min_price = $row['min_price'];
                    if ($is_flashsale) {
                        $discount_value = $flashsale_data[$product_id]['discount_value'];
                        $discount_type = $flashsale_data[$product_id]['discount_type'];
                        $remaining_quantity = $flashsale_data[$product_id]['remaining_quantity'];
                        if ($discount_type == 'percentage') {
                            $original_price = $min_price / (1 - ($discount_value / 100));
                        } else {
                            $original_price = $min_price + $discount_value;
                        }
                    }

                    echo '<div class="product">';
                    echo '<a href="index.php?product_id=' . $product_id . '" class="product-link">';
                    echo '<div class="product-image">';
                echo '<img src="Uploads/' . htmlspecialchars($default_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="default-image">';
                echo '<img src="Uploads/' . htmlspecialchars($hover_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="hover-image">';
                if ($is_flashsale) {
                    echo '<img src="public/images/flashsale.webp" alt="Flash Sale"class="discount-badge" style="    width: 50px;
height: 50px;
top: -10px;"">';
                }
                echo '</div>';
                    echo '<h3>' . htmlspecialchars($row['product_name']) . ' <span class="rating">(' . number_format($avg_rating, 1) . '<i class="fa-solid fa-star"></i>)</span></h3>';
                    
                    echo '<div class="price-sales">';
                    echo '<p class="price">';
                    if ($is_flashsale) {
                        echo '<span class="original-price">' . number_format($original_price, 0, ',', '.') . '</span> ';
                    }
                    echo number_format($row['min_price'], 0, ',', '.') . '</p>';
                    echo '<div class="price-details" style="    display: flex;     justify-content: space-between;">';
                 
                    echo '<span class="sales">Đã bán: ' . $row['sales_count'] . '</span>';
                    if ($is_flashsale) {
                        echo '<span class="remaining">Còn lại: ' . $remaining_quantity . '</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                $best_sellers_result->free();
                ?>
            </div>
            <button class="slider-btn next" onclick="slide('best-sellers', 1)">❯</button>
        </div>
    </div>

    <!-- Sản phẩm được đánh giá cao -->
    <div class="top-rated-container" data-aos="fade-up">
        <h2>SẢN PHẨM ĐƯỢC ĐÁNH GIÁ CAO</h2>
        <div class="slider-wrapper">
            <button class="slider-btn prev" onclick="slide('top-rated', -1)">❮</button>
            <div class="horizontal-scroll" id="top-rated">
                <?php
                while ($row = $top_rated_result->fetch_assoc()) {
                    $product_id = $row['product_id'];
                    $is_favorite = isset($favorite_status[$product_id]) && $favorite_status[$product_id];

                    // Lấy ảnh đầu tiên và ảnh thứ hai từ tbl_product_images
                    $image_query = "SELECT image_url 
                                    FROM tbl_product_images 
                                    WHERE product_id = ? 
                                    ORDER BY image_id ASC 
                                    LIMIT 2";
                    $image_stmt = $conn->prepare($image_query);
                    $image_stmt->bind_param("i", $product_id);
                    $image_stmt->execute();
                    $image_result = $image_stmt->get_result();
                    $images = $image_result->fetch_all(MYSQLI_ASSOC);
                    $image_stmt->close();

                    $default_image = $images[0]['image_url'] ?? 'Uploads/no-image.png';
                    $hover_image = $images[1]['image_url'] ?? $default_image;

                    // Lấy đánh giá trung bình
                    $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;

                    // Lấy variants từ tbl_product_variants
                    $variant_query = "SELECT ram, storage FROM tbl_product_variants WHERE product_id = ?";
                    $variant_stmt = $conn->prepare($variant_query);
                    $variant_stmt->bind_param("i", $product_id);
                    $variant_stmt->execute();
                    $variant_result = $variant_stmt->get_result();
                    $variants = [];
                    while ($variant = $variant_result->fetch_assoc()) {
                        $variants[] = $variant['ram'] . " / " . $variant['storage'];
                    }
                    $variant_stmt->close();

                    // Kiểm tra sản phẩm có trong Flash Sale không
                    $is_flashsale = isset($flashsale_data[$product_id]);
                    $original_price = $row['original_price'];
                    $min_price = $row['min_price'];
                    if ($is_flashsale) {
                        $discount_value = $flashsale_data[$product_id]['discount_value'];
                        $discount_type = $flashsale_data[$product_id]['discount_type'];
                        $remaining_quantity = $flashsale_data[$product_id]['remaining_quantity'];
                        if ($discount_type == 'percentage') {
                            $original_price = $min_price / (1 - ($discount_value / 100));
                        } else {
                            $original_price = $min_price + $discount_value;
                        }
                    }

                    echo '<div class="product">';
                    echo '<a href="index.php?product_id=' . $product_id . '" class="product-link">';
                    echo '<div class="product-image">';
                echo '<img src="Uploads/' . htmlspecialchars($default_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="default-image">';
                echo '<img src="Uploads/' . htmlspecialchars($hover_image) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="hover-image">';
                if ($is_flashsale) {
                    echo '<img src="public/images/flashsale.webp" alt="Flash Sale"class="discount-badge" style="    width: 50px;
height: 50px;
top: -10px;"">';
                }
                echo '</div>';
                    echo '<h3>' . htmlspecialchars($row['product_name']) . ' <span class="rating">(' . number_format($avg_rating, 1) . '<i class="fa-solid fa-star"></i>)</span></h3>';
                    
                    echo '<div class="price-sales">';
                    echo '<p class="price">';
                    if ($is_flashsale) {
                        echo '<span class="original-price">' . number_format($original_price, 0, ',', '.') . '</span> ';
                    }
                    echo number_format($row['min_price'], 0, ',', '.') . '</p>';
                    echo '<div class="price-details" style="    display: flex;     justify-content: space-between;">';
                    echo '<span class="sales">Đã bán: ' . $row['sales_count'] . '</span>';
                    if ($is_flashsale) {
                        echo '<span class="remaining">Còn lại: ' . $remaining_quantity . '</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                $top_rated_result->free();
                ?>
            </div>
            <button class="slider-btn next" onclick="slide('top-rated', 1)">❯</button>
        </div>
    </div>

    <script>
    // Hàm toggle bộ lọc
    function toggleFilter() {
        const filterContent = document.querySelector('.filter-content');
        const toggleText = document.querySelector('.toggle-text');
        const toggleIcon = document.querySelector('.toggle-icon');

        filterContent.classList.toggle('collapsed');
        if (filterContent.classList.contains('collapsed')) {
            toggleText.textContent = 'MỞ RỘNG';
            toggleIcon.textContent = '▲';
        } else {
            toggleText.textContent = 'THU GỌN';
            toggleIcon.textContent = '▼';
        }
    }

    // Xử lý responsive cho bộ lọc
    window.addEventListener('load', function() {
        const filterContent = document.querySelector('.filter-content');
        const toggleText = document.querySelector('.toggle-text');
        const toggleIcon = document.querySelector('.toggle-icon');

        if (window.innerWidth <= 600) {
            filterContent.classList.add('collapsed');
            toggleText.textContent = 'MỞ RỘNG';
            toggleIcon.textContent = '▲';
        } else {
            filterContent.classList.remove('collapsed');
            toggleText.textContent = 'THU GỌN';
            toggleIcon.textContent = '▼';
        }
    });

    window.addEventListener('resize', function() {
        const filterContent = document.querySelector('.filter-content');
        const toggleText = document.querySelector('.toggle-text');
        const toggleIcon = document.querySelector('.toggle-icon');

        if (window.innerWidth <= 600) {
            if (!filterContent.classList.contains('collapsed')) {
                filterContent.classList.add('collapsed');
                toggleText.textContent = 'MỞ RỘNG';
                toggleIcon.textContent = '▲';
            }
        } else {
            if (filterContent.classList.contains('collapsed')) {
                filterContent.classList.remove('collapsed');
                toggleText.textContent = 'THU GỌN';
                toggleIcon.textContent = '▼';
            }
        }
    });

    // Quản lý vị trí hiện tại của slider
    const sliderPositions = {
        'best-sellers': 0,
        'top-rated': 0
    };

    // Số sản phẩm hiển thị tại một thời điểm
    const itemsPerPage = 5;

    function slide(sliderId, direction) {
        const slider = document.getElementById(sliderId);
        const items = slider.querySelectorAll('.product');
        const totalItems = items.length;

        // Cập nhật vị trí slider
        sliderPositions[sliderId] += direction * itemsPerPage;

        // Giới hạn vị trí
        if (sliderPositions[sliderId] < 0) {
            sliderPositions[sliderId] = 0;
        }
        if (sliderPositions[sliderId] > totalItems - itemsPerPage) {
            sliderPositions[sliderId] = Math.max(0, totalItems - itemsPerPage);
        }

        // Di chuyển slider
        const itemWidth = items[0].offsetWidth + 5; // 5px là gap
        slider.style.transform = `translateX(-${sliderPositions[sliderId] * itemWidth}px)`;

        // Ẩn/hiện nút tiến lùi
        const prevBtn = slider.parentElement.querySelector('.prev');
        const nextBtn = slider.parentElement.querySelector('.next');
        prevBtn.style.display = sliderPositions[sliderId] === 0 ? 'none' : 'block';
        nextBtn.style.display = sliderPositions[sliderId] >= totalItems - itemsPerPage ? 'none' : 'block';
    }

    // Khởi tạo slider khi trang tải xong
    document.addEventListener('DOMContentLoaded', () => {
        ['best-sellers', 'top-rated'].forEach(sliderId => {
            const slider = document.getElementById(sliderId);
            if (slider) {
                const items = slider.querySelectorAll('.product');
                const totalItems = items.length;
                const prevBtn = slider.parentElement.querySelector('.prev');
                const nextBtn = slider.parentElement.querySelector('.next');
                prevBtn.style.display = 'none'; // Ẩn nút "prev" ban đầu
                nextBtn.style.display = totalItems <= itemsPerPage ? 'none' : 'block';
            }
        });
    });

    async function toggleFavorite(event, productId) {
        event.preventDefault();
        event.stopPropagation();

        const userId = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
        if (!userId) {
            window.location.href = 'login.php';
            return;
        }

        const icon = document.querySelector(`.favorite-icon[data-product-id="${productId}"]`);
        const formData = new FormData();
        formData.append('action', 'toggle_favorite');
        formData.append('product_id', productId);

        try {
            const response = await fetch('views/ajax_handler.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                if (data.is_favorite) {
                    icon.classList.add('liked');
                    icon.title = 'Xóa khỏi yêu thích';
                } else {
                    icon.classList.remove('liked');
                    icon.title = 'Thêm vào yêu thích';
                }
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
    </script>

    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
    }

    .main-container {
        display: flex;
        gap: 5px;
        width: 100%;
        margin: 5px;
    }

    .product-container {
        width: 100%;
        border-radius: 5px;
        padding: 10px;
    }

    .product-list {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        justify-content: flex-start;
    }

    .product {
        width: 214px;
        background-color: #fff;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        font-family: 'Montserrat', sans-serif;
        text-transform: uppercase;
        position: relative;
    }

    .product:hover {
        border: 1px solid black;
    }

    .product-link {
        text-decoration: none;
        color: inherit;
        display: block;
        padding: 5px;
    }

    .product-image {
        position: relative;
        width: 100%;
        height: 180px;
        overflow: hidden;
        border-radius: 8px;
        background-color: #fff;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: opacity 0.4s ease;
    }

    .default-image {
        position: absolute;
        top: 0;
        left: 0;
    }
    .rating{
        color: gold;
    }
    .hover-image {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
    }

    .product:hover .default-image {
        opacity: 0;
    }

    .product:hover .hover-image {
        opacity: 1;
    }

    .flashsale-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 50px;
        height: auto;
        z-index: 10;
    }

    .favorite-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 18px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10;
        border: none;
        background: none;
        padding: 5px;
    }

    .favorite-icon.liked {
        color: #e74c3c;
    }

    .favorite-icon:hover {
        color: #e74c3c;
    }

    .product h3 {
        font-size: 16px;
        color: #333;
        font-weight: 600;
        line-height: 1.4;

    }

    .star {
        font-size: 20px;
    }

    .star.full {
        color: #f1c40f;
    }

    .star.half {
        color: #f1c40f;
    }

    .star.empty {
        color: #ccc;
    }

    .price-sales {
        font-size: 14px;
        flex-direction: column;
        display: flex;  
        justify-content: space-between;

    }
 .price-sales1 {
        font-size: 14px;
     

    }
    .price {
        display: flex
;
    color: #e74c3c;
    font-weight: bold;
    font-size: 15px;
    justify-content: space-between;
    }
        .price1 {
        display: flex
;
    color: #e74c3c;
    font-weight: bold;
    font-size: 15px;
    justify-content: space-between;
    }
    p {
    margin-top: 0;
    margin-bottom: 0 !important;
}
    .original-price {
        color: #999;
        text-decoration: line-through;
        font-size: 13px;
    }

    .sales {
        display: flex
;
    font-size: 12px;
    color: #555;
    font-weight: 600;
    padding: 2.5px;
    justify-content: flex-end;
    }

    .remaining {
        font-size: 12px;
        color: #e74c3c;
        font-weight: 600;
    }

    .variants {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
        margin-top: 5px;
    }

    .variant {
        background-color: #f1f1f1;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        color: #333;
    }

    .filter-sidebar {
        flex: 2;
        border-radius: 5px;
        padding: 20px;
        text-transform: uppercase;
        font-weight: 500;
    }

    .product-main {
        flex: 8;
    }

    .filter-sidebar h3 {
        font-size: 18px;
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
    }

    .filter-group {
        margin-bottom: 20px;
    }

    .filter-group h4 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #333;
        font-weight: 500;
    }

    .filter-group input[type="text"],
    .filter-group input[type="number"],
    .filter-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        outline: none;
        text-transform: uppercase;
        font-weight: 500;
    }

    .price-range {
        display: flex;
        gap: 10px;
    }

    .price-range input {
        flex: 1;
    }

    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #555;
    }

    .filter-group input[type="checkbox"] {
        margin-right: 8px;
    }

    .filter-btn {
        width: 100%;
        padding: 10px;
        background-color: #000000FF;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    .filter-btn:hover {
        background-color: #343434FF;
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .toggle-filter-btn {
        background: none;
        border: none;
        font-size: 14px;
        color: #000000FF;
        cursor: pointer;
        display: flex;
        margin-bottom: 20px;
        align-items: center;
        gap: 5px;
        transition: color 0.3s ease;
    }

    .toggle-filter-btn:hover {
        color: #000000FF;
    }

    .toggle-icon {
        font-size: 12px;
    }

    .filter-content {
        transition: max-height 0.3s ease, opacity 0.3s ease;
        max-height: 1000px;
        opacity: 1;
    }

    .filter-content.collapsed {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
    }

    @media (max-width: 600px) {
        .filter-content {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        .filter-content:not(.collapsed) {
            max-height: 1000px;
            opacity: 1;
        }
    }

    .category-header {
        margin-bottom: 15px;
    }

    .category-header h2 {
        font-size: 20px;
        color: #333;
        margin: 0 0 10px 0;
        text-align: center;
        text-transform: uppercase;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
    }

    .category-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .category-item {
        flex: 0 0 auto;
    }

    .category-link {
        text-decoration: none;
        color: #000000FF;
        font-size: 14px;
        padding: 8px 15px;
        background-color: #f1f1f1;
        border-radius: 20px;
        transition: background-color 0.3s ease, color 0.3s ease;
        display: block;
    }

    .category-link:hover {
        background-color: #000000FF;
        color: #fff;
    }

    .category-link.active {
        background-color: #000000FF;
        color: #fff;
    }

    .best-sellers-container, .top-rated-container {
        width: 100%;
        border-radius: 5px;
        padding: 10px;
        margin: 5px;
    }
    .discount-badge {
    position: absolute;
    top: 10px;
    z-index: 10;
}

    .best-sellers-container h2, .top-rated-container h2 {
        font-size: 20px;
        color: #333;
        margin: 0 0 10px 0;
        font-family: 'Montserrat', sans-serif;
        text-align: center;
        font-weight: 700;
    }

    .slider-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .horizontal-scroll {
        display: flex;
        gap: 5px;
        margin-bottom: 5px;
        transition: transform 0.5s ease;
        will-change: transform;
        justify-content: center;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
        font-size: 18px;
        z-index: 10;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .slider-btn.prev {
        left: 10px;
    }

    .slider-btn.next {
        right: 10px;
    }

    .slider-btn:hover {
        background: rgba(0, 0, 0, 0.8);
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .page-link {
        text-decoration: none;
        padding: 8px 12px;
        background-color: #f1f1f1;
        color: #333;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .page-link:hover {
        background-color: #000000FF;
        color: #fff;
    }

    .page-link.active {
        background-color: #000000FF;
        color: #fff;
    }

    @media (max-width: 900px) {
        .main-container {
            flex-direction: column;
            margin: 0;
            padding: 5px;
        }
.horizontal-scroll{
    display: flex;
    flex-wrap: wrap;
}
        .filter-sidebar {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
        }

        .product-main {
            width: 100%;
            padding: 0;
        }

        .product-container {
            padding: 5px;
        }
    }

    @media (max-width: 600px) {
        .product-list {
            gap: 5px;
        }
.horizontal-scroll{
    display: flex;
    flex-wrap: wrap;
}
        .product {
            width: calc(50% - 5px);
            padding: 5px;
        }

        .product-image {
            height: 150px;
        }

        .product h3 {
            font-size: 14px;
            height: 45px;
        }

        .price {
            font-size: 14px;
        }

        .variants .variant {
            font-size: 10px;
            padding: 3px 6px;
        }

        .category-list {
            gap: 5px;
        }

        .category-link {
            font-size: 12px;
            padding: 6px 10px;
        }

        .favorite-icon {
            font-size: 16px;
            top: 8px;
            right: 8px;
        }
    }@media (max-width: 576px) {
    .product {
        flex: unset !important; 
        max-width: calc(50% - 1px);
    }
    .horizontal-scroll{
    display: flex;
    flex-wrap: wrap;
}
}

    @media (max-width: 600px) {
        .best-sellers-container .horizontal-scroll .product,
        .top-rated-container .horizontal-scroll .product {
            width: calc(50% - 5px);
            min-width: 160px;
            padding: 5px;
        }

        .best-sellers-container .product-image,
        .top-rated-container .product-image {
            height: 150px;
        }

        .best-sellers-container .product h3,
        .top-rated-container .product h3 {
            font-size: 14px;
            height: 36px;
        }

        .best-sellers-container .price,
        .top-rated-container .price {
            font-size: 14px;
        }

        .best-sellers-container .variants .variant,
        .top-rated-container .variants .variant {
            font-size: 10px;
            padding: 3px 6px;
        }

        .slider-btn {
            width: 30px;
            height: 30px;
            font-size: 14px;
        }

        .slider-btn.prev {
            left: 5px;
        }

        .slider-btn.next {
            right: 5px;
        }
    }

    @media (min-width: 601px) and (max-width: 900px) {
        .best-sellers-container .horizontal-scroll .product,
        .top-rated-container .horizontal-scroll .product {
            width: calc(33.33% - 5px);
            min-width: 200px;
        }
    }

    @media (min-width: 901px) {
        .best-sellers-container .horizontal-scroll .product,
        .top-rated-container .horizontal-scroll .product {
        }
    }

    /* Tùy chỉnh SweetAlert2 Toast */
    .swal2-toast {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
    }
    .swal2-toast .swal2-title {
        color: #333;
        font-size: 16px;
    }
    .swal2-toast .swal2-success-ring {
        border-color: #28a745 !important;
    }
    
    </style>
</body>
</html>
<?php
}
$conn->close();
?>