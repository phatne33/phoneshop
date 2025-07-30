<?php
// Đặt múi giờ cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

// Truy vấn để lấy các Flash Sale đang hoạt động và sản phẩm của chúng
$flashsale_query = "
    SELECT fs.flashsale_id, fs.title, fs.start_time, fs.end_time,
           f.id, f.product_id, f.max_quantity, f.sold_quantity,
           p.product_name, p.sales_count,
           MIN(pv.price) as discounted_price,
           MIN(pv.original_price) as original_price
    FROM tbl_flashsales fs
    JOIN tbl_flashsale f ON fs.flashsale_id = f.flashsale_id
    JOIN tbl_products p ON f.product_id = p.product_id
    JOIN tbl_product_variants pv ON p.product_id = pv.product_id
    WHERE fs.status = 'active'
    AND fs.start_time <= NOW()
    AND fs.end_time >= NOW()
    GROUP BY p.product_id
    ORDER BY fs.start_time DESC
";

$flashsale_stmt = $conn->prepare($flashsale_query);
if (!$flashsale_stmt) {
    die("Prepare failed: " . $conn->error);
}
$flashsale_stmt->execute();
$flashsale_result = $flashsale_stmt->get_result();

// Lấy title của Flash Sale đầu tiên (nếu có)
$flashsale_title = "FLASH SALE";
$end_time = null;
if ($flashsale_result->num_rows > 0) {
    $first_row = $flashsale_result->fetch_assoc();
    $flashsale_title = !empty($first_row['title']) ? htmlspecialchars($first_row['title']) : "FLASH SALE";
    $end_time = $first_row['end_time'];
    $flashsale_result->data_seek(0); // Đặt con trỏ kết quả về đầu
}
?>

<div class="flashsale-container" data-aos="fade-up" style="padding: 8px;">
    <!-- Header Flash Sale -->
    <div class="flashsale-header">
        <h2>📢💥 <?php echo $flashsale_title; ?></h2>
        <div class="countdown-timer" id="countdown-timer">
            <span id="timer-days">00</span> :
            <span id="timer-hours">00</span> :
            <span id="timer-minutes">00</span> :
            <span id="timer-seconds">00</span>
        </div>
    </div>

    <!-- Slider Sản phẩm Flash Sale -->
    <div class="slider-wrapper">
        <div class="product-rows" id="product-rows">
            <?php
            if ($flashsale_result->num_rows > 0) {
                $products = [];
                while ($row = $flashsale_result->fetch_assoc()) {
                    $products[] = $row;
                }
                $flashsale_result->free();

                // Chia sản phẩm thành các hàng, mỗi hàng tối đa 7 sản phẩm
                $products_per_row = 7;
                $rows = array_chunk($products, $products_per_row);

                // Hiển thị các hàng sản phẩm
                foreach ($rows as $row_index => $row_products) {
                    $is_hidden = $row_index > 0 ? 'hidden' : '';
                    echo "<div class='product-row $is_hidden' data-row='$row_index'>";
                    foreach ($row_products as $row) {
                        $product_id = $row['product_id'];
                        $max_quantity = $row['max_quantity'];
                        $sold_quantity = $row['sold_quantity'];
                        $remaining_quantity = $max_quantity - $sold_quantity;
                        $discounted_price = $row['discounted_price'];
                        $original_price = $row['original_price'];

                        // Lấy ảnh sản phẩm
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

                        // Lấy đánh giá trung bình và số lượng đánh giá
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

                        // Lấy các biến thể sản phẩm
                        $variant_query = "SELECT ram, storage 
                                         FROM tbl_product_variants 
                                         WHERE product_id = ?";
                        $variant_stmt = $conn->prepare($variant_query);
                        $variant_stmt->bind_param("i", $product_id);
                        $variant_stmt->execute();
                        $variant_result = $variant_stmt->get_result();
                        $variants = [];
                        while ($variant = $variant_result->fetch_assoc()) {
                            $variant_str = trim($variant['ram'] . " / " . $variant['storage']);
                            if (!empty($variant_str)) {
                                $variants[] = $variant_str;
                            }
                        }
                        $variant_stmt->close();

                        // Kiểm tra sản phẩm có trong danh sách yêu thích
                        $is_favorite = false;
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            $favorite_query = "SELECT COUNT(*) as is_favorite 
                                              FROM tbl_favorites 
                                              WHERE user_id = ? AND product_id = ?";
                            $favorite_stmt = $conn->prepare($favorite_query);
                            $favorite_stmt->bind_param("ii", $user_id, $product_id);
                            $favorite_stmt->execute();
                            $favorite_result = $favorite_stmt->get_result();
                            $is_favorite = $favorite_result->fetch_assoc()['is_favorite'] > 0;
                            $favorite_stmt->close();
                        }
                        ?>
                        <div class="product">
                            <!-- Badge giảm giá -->
                            <div class="discount-badge">
                                <img src="public/images/flashsale.webp" alt="Giảm giá" style="width: 50px;">
                            </div>

                            <a href="index.php?product_id=<?php echo $product_id; ?>" class="product-link">
                                <div class="product-image">
                                    <img src="Uploads/<?php echo htmlspecialchars($default_image); ?>" 
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                         class="default-image">
                                    <img src="Uploads/<?php echo htmlspecialchars($hover_image); ?>" 
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                         class="hover-image">
                                </div>
                            </a>
                            <a href="index.php?product_id=<?php echo $product_id; ?>" class="product-link">
                                <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                <p class="original-price"><?php echo number_format($original_price, 0, ',', '.'); ?></p>

                                <div class="price-sales">
                                    <p class="price"><?php echo number_format($discounted_price, 0, ',', '.'); ?></p>
                                    <?php if ($original_price && $original_price > $discounted_price): ?>
                                    <?php endif; ?>
                                    <span class="sales">Đã bán: <?php echo $row['sales_count']; ?></span>
                                </div>
                                <div class="remaining-quantity">
                                    <span>Còn lại: <?php echo $remaining_quantity; ?> sản phẩm</span>
                                </div>
                               
                            </a>
                        </div>
                        <?php
                    }
                    echo "</div>";
                }
            } 
            ?>
        </div>
        <?php if (isset($rows) && count($rows) > 1) : ?>
            <div class="toggle-button-container">
                <button id="toggle-rows" class="toggle-button">Xem thêm</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Script đếm ngược
function startCountdown(endTime) {
    const timer = setInterval(function() {
        const now = new Date().getTime();
        const end = new Date(endTime).getTime();
        const distance = end - now;

        if (distance < 0) {
            clearInterval(timer);
            document.getElementById("countdown-timer").innerHTML = "Flash Sale đã kết thúc!";
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("timer-days").textContent = days.toString().padStart(2, "0");
        document.getElementById("timer-hours").textContent = hours.toString().padStart(2, "0");
        document.getElementById("timer-minutes").textContent = minutes.toString().padStart(2, "0");
        document.getElementById("timer-seconds").textContent = seconds.toString().padStart(2, "0");
    }, 1000);
}

// Script xử lý nút "Xem thêm"/"Ẩn bớt"
document.addEventListener("DOMContentLoaded", function() {
    const toggleButton = document.getElementById("toggle-rows");
    const productRows = document.querySelectorAll(".product-row");

    if (toggleButton) {
        toggleButton.addEventListener("click", function() {
            const isExpanded = toggleButton.textContent === "Ẩn bớt";
            
            if (isExpanded) {
                productRows.forEach(row => {
                    if (row.getAttribute("data-row") > 0) {
                        row.classList.add("hidden");
                    }
                });
                toggleButton.textContent = "Xem thêm";
            } else {
                productRows.forEach(row => {
                    row.classList.remove("hidden");
                });
                toggleButton.textContent = "Ẩn bớt";
            }
        });
    }
});

// Bắt đầu đếm ngược nếu có thời gian kết thúc
<?php if ($end_time) : ?>
    startCountdown("<?php echo $end_time; ?>");
<?php else : ?>
    document.getElementById("countdown-timer").innerHTML = "";
<?php endif; ?>
</script>

<style>
/* CSS cho Flash Sale */
.flashsale-container {
}

.flashsale-header {
    display: flex;
    height: 70px;
    border-radius: 10px;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    background: black;
    padding: 10px;
}

.flashsale-header h2 {
    font-size: 24px;
    font-weight: bold;
    color: white;
    margin-bottom: 0;
}

.countdown-timer {
    font-size: 18px;
    color: #e74c3c;
    padding: 5px;
    background: white;
    border-radius: 5px;
}

.countdown-timer span {
    font-weight: bold;
}

.slider-wrapper {
    position: relative;
}

.product-rows {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.product-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1px;
}

.product-row.hidden {
    display: none;
}

.product {
    position: relative;
    box-sizing: border-box;
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.discount-badge {
    position: absolute;
    top: 10px;
    z-index: 10;
}

.product-image {
    position: relative;
    width: 100%;
    padding-top: 100%; /* Tỷ lệ 1:1 */
    overflow: hidden;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s;
}

.product-image .hover-image {
    opacity: 0;
}

.product:hover .default-image {
    opacity: 0;
}

.product:hover .hover-image {
    opacity: 1;
}

.product-link {
    text-decoration: none;
    color: inherit;
}

.product h3 {
    font-size: 16px;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 45px;
}

.star {
    font-size: 14px;
    color: #ccc;
}

.star.full, .star.half {
    color: #f1c40f;
}



.price {
    font-size: 16px;
    font-weight: bold;
    color: #e74c3c;
}

.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 14px;
}

.sales {
    font-size: 12px;
    color: #666;
}

.remaining-quantity {
    margin-top: 5px;
    font-size: 14px;
    color: #e74c3c;
}

.variants {
    margin-top: 5px;
}

.variant {
    display: inline-block;
    font-size: 12px;
    color: #666;
    border: 1px solid #ddd;
    padding: 2px 5px;
    margin: 2px;
    border-radius: 3px;
}

.toggle-button-container {
    text-align: center;
    margin-top: 20px;
}

.toggle-button {
    padding: 10px 20px;
    background-color: #000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.toggle-button:hover {
    background-color: #202020;
}

/* Responsive */
@media (max-width: 1200px) {
    .product {
        flex: 1 1 calc(25% - 1px); /* 4 sản phẩm */
        max-width: calc(25% - 1px);
    }
}

@media (max-width: 768px) {
    .product {
        flex: 1 1 calc(33.33% - 1px); /* 3 sản phẩm */
        max-width: calc(33.33% - 1px);
    }
}

@media (max-width: 576px) {
    .product {
        flex: 1 1 calc(50% - 1px); /* 2 sản phẩm */
        max-width: calc(50% - 1px);
    }

    .flashsale-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .countdown-timer {
        margin-top: 10px;
    }
}

@media (max-width: 400px) {
    .product {
        flex: 1 1 100%; /* 1 sản phẩm */
        max-width: 100%;
    }
}
</style>

