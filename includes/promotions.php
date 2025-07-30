<?php
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$sql = "SELECT promo_code, discount_value, discount_type, start_date, end_date, status 
        FROM tbl_promotions 
        WHERE status = 'active'";
$result = mysqli_query($conn, $sql);
$promotions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $promotions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Mã Giảm Giá</title>
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
    <style>
   
        .promo-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .main-carousel {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        .carousel-cell {
            width: 300px;
            height: 180px;
            margin-right: 15px;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border: 2px dashed #e67e22;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .carousel-cell:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .promo-image {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
        }
        .promo-code {
            font-size: 22px;
            font-weight: bold;
            color: #e74c3c;
            background: rgba(255, 255, 255, 0.8);
            padding: 5px 15px;
            border-radius: 5px;
            margin: 5px 0;
            letter-spacing: 1px;
        }
        .promo-details {
            font-size: 14px;
            color: #2c3e50;
        }
        .promo-details span {
            display: block;
            margin: 3px 0;
        }
        .copy-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .copy-btn:hover {
            background-color: #2980b9;
        }
        .flickity-button {
            display: block; /* Hiển thị nút điều hướng */
            background: rgba(255, 255, 255, 0.75);
            border: none;
            color: #333;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            transition: background-color 0.3s ease;
        }
        .flickity-button:hover {
            background: rgba(255, 255, 255, 1);
        }
        .flickity-prev-next-button.previous {
            left: 10px;
        }
        .flickity-prev-next-button.next {
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="promo-container" data-aos="fade-up">
        <div class="main-carousel" data-flickity='{ "cellAlign": "left", "contain": true, "autoPlay": 3000, "prevNextButtons": true, "pageDots": false, "wrapAround": true }'>
            <?php foreach ($promotions as $promo): ?>
                <div class="carousel-cell">
                    <img class="promo-image" src="public/images/prom.png" alt="Promo Icon">
                    <div class="promo-code"><?= htmlspecialchars($promo['promo_code']) ?></div>
                    <div class="promo-details">
                        <span>Mã giảm giá</span>
                        <span>Giảm: <?= $promo['discount_type'] === 'percentage' 
                            ? number_format($promo['discount_value'], 2) . '%' 
                            : number_format($promo['discount_value']) . ' VNĐ' ?></span>
                        <span>Hạn: <?= date('d/m/Y', strtotime($promo['end_date'])) ?></span>
                    </div>
                    <button class="copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($promo['promo_code']) ?>')">Copy</button>
                </div>
            <?php endforeach; ?>
            <?php if (empty($promotions)): ?>
                <div class="carousel-cell">
                    <div class="promo-details">
                        <span>Hiện tại chưa có mã giảm giá nào</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Đã sao chép mã giảm giá: ' + text);
            }).catch(err => {
                console.error('Lỗi khi sao chép: ', err);
                alert('Không thể sao chép mã giảm giá!');
            });
        }
    </script>
</body>
</html> 