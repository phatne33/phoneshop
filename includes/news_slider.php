<?php
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$sql = "SELECT news_id, title, image_url FROM tbl_news ORDER BY published_at DESC";
$result = $conn->query($sql);
$news_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $news_items[] = $row;
    }
} else {
    echo "Không có bài viết nào trong bảng tbl_news.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức Điện Thoại</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .news-container {
        }
        .bx-slider-container {
            background: transparent;
            display: flex;
            justify-content: center;
            border-radius: 15px;
        }
        .bx-news-item {
            width: 250px;
            height: 300px;
            background: #fff;
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s;
            border: 1px solid #333;
        }
        .bx-news-item:hover {
            border: 1px solid black;
        }
        .bx-news-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .bx-news-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bx-news-link {
            display: inline-block;
            margin: 10px;
            padding: 5px 15px;
            background: #000000FF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .bx-news-link:hover {
            background: #282828FF;
        }.bx-wrapper {
    border: 5px solid #fff;
    background: #fff;
    max-width: 100% !important;
    padding: 10px;
    border-radius: 5px;
    box-shadow: none !important;

}
.bx-wrapper .bx-controls-direction a {
    display: none;
}
    </style>
</head>
<body>
</div>

    <div class="news-container" data-aos="fade-right">
        <?php if (empty($news_items)): ?>
            <p style="text-align: center;">Chưa có bài viết nào.</p>
        <?php else: ?>
            <div class="bx-slider-container">
                <div class="bx-news-slider">
                    <?php foreach ($news_items as $item): ?>
                        <div class="bx-news-item">
                        <img class="bx-news-image" src="public/images/<?php echo htmlspecialchars(preg_replace('/^\d+_/', '', $item['image_url'] ?? 'default.jpg')); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">                           
                         <div class="bx-news-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            <a href="http://localhost/phone-shop/news_details.php?id=<?php echo $item['news_id']; ?>" class="bx-news-link">Xem chi tiết </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.bx-news-slider').bxSlider({
                mode: 'horizontal',
                slideWidth: 250,
                minSlides: 1,
                maxSlides: 4,
                moveSlides: 1,
                slideMargin: 15,
                auto: true,
                pause: 3000,
                pager: false,
                controls: true,
                infiniteLoop: true,
                touchEnabled: false // Ngăn chặn bxSlider chặn click
            });

            // Xử lý click thủ công
            $('.bx-news-link').on('click', function(e) {
                e.preventDefault();
                window.location.href = $(this).attr('href');
            });
        });
    </script>
</body>
</html>