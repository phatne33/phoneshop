<?php
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn lấy category_id, category_name và category_logo (nếu có)
$sql = "SELECT category_id, category_name, category_logo FROM tbl_categories ORDER BY display_order ASC";
$result = mysqli_query($conn, $sql);
$categories = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Xử lý tên file logo: bỏ dãy số và dấu gạch dưới ở đầu (nếu có)
    $logo = isset($row['category_logo']) && !empty($row['category_logo']) 
            ? preg_replace('/^\d+_/', '', $row['category_logo']) 
            : 'no-image.png'; // Ảnh mặc định nếu không có logo
    $categories[] = [
        'id' => $row['category_id'],
        'name' => $row['category_name'],
        'logo' => $logo
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo Danh Mục</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css"/>
    <style>
        .slider-container {
            width: 100%;
            padding: 20px 0;
            border-radius: 5px;
            margin: 5px;
        }
        .slick-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 2px;
        }
        .slick-list {
            padding: 0 !important;
        }
        .logo-box {
            position: relative; /* Để tooltip định vị đúng */
            text-align: center;
        }
        .logo-category {
            width: 100%;
            max-width: 80px;
            height: 60px;
            object-fit: contain;
            background-color: white;
            transition: transform 0.3s ease; /* Hiệu ứng khi hover */
        }
        .logo-box:hover .logo-category {
            transform: scale(1.1); /* Phóng to nhẹ khi hover */
        }
        /* Tooltip */
        .logo-box .tooltip {
            visibility: hidden;
            width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 100%; /* Hiển thị phía trên logo */
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }
        .logo-box:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
        /* Nút điều hướng */
        .slick-prev, .slick-next {
            font-size: 0; /* Ẩn chữ mặc định */
            width: 30px;
            height: 30px;
            z-index: 1000;
        }
        .slick-prev {
            left: -15px;
        }
        .slick-next {
            right: 40px;
        }
        .slick-prev:before, .slick-next:before {
            font-family: 'slick';
            font-size: 20px;
            line-height: 1;
            opacity: 0; /* Ẩn nút mặc định, có thể bỏ nếu muốn hiện */
            color: #333; /* Màu xám đậm */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .slick-prev:before {
            content: '◄';
        }
        .slick-next:before {
            content: '►';
        }
    </style>
</head>
<body>

<div class="slider-container">
    <div class="slick-slider">
        <?php foreach ($categories as $category): ?>
            <div class="logo-box">
                <a href="index.php?category_id=<?= htmlspecialchars($category['id']) ?>">
                    <!-- Đường dẫn ảnh từ thư mục uploads -->
                    <img class="logo-category" src="http://localhost/phone-shop/uploads/<?= htmlspecialchars($category['logo']) ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                    <span class="tooltip">Nhấp vào để xem điện thoại <?= htmlspecialchars($category['name']) ?></span>
                </a>
            </div>  
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>
<script>
    $(document).ready(function(){
        $('.slick-slider').slick({
            slidesToShow: 6,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 3000,
            arrows: true,
            prevArrow: '<button type="button" class="slick-prev"></button>',
            nextArrow: '<button type="button" class="slick-next"></button>',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    });
</script>

</body>
</html>