<?php
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($news_id <= 0) {
    die("Bài viết không tồn tại.");
}

$sql = "SELECT title, content, image_url, published_at FROM tbl_news WHERE news_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    die("Bài viết không tồn tại.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .news-detail-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .news-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .news-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .news-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .news-content {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
<?php 
$image_url = htmlspecialchars($news['image_url']);
$image_url_without_number_and_underscore = preg_replace('/^\d+/', '', $image_url); // Remove any digits at the start
$image_url_without_underscore = str_replace('_', '', $image_url_without_number_and_underscore); // Remove underscores
?>
    <div class="news-detail-container">
        <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>
        <div class="news-date">Đăng ngày: <?php echo date('d/m/Y H:i', strtotime($news['published_at'])); ?></div>
        <?php if ($news['image_url']): ?>
            <img class="news-image" src="public/images/<?php echo $image_url_without_underscore; ?>" alt="<?php echo htmlspecialchars($news['title']); ?>">
            <?php endif; ?>
        <div class="news-content"><?php echo nl2br(htmlspecialchars($news['content'])); ?></div>
        <a href="index.php" class="back-link">Quay lại</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>