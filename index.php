<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu là yêu cầu AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include 'ajax_handler.php';
    exit;
}
include 'includes/menu.php';
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="vi" style="width: 100%; overflow-x: hidden;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <?php     
    include 'includes/main.php';    
    include 'includes/footer.php'; 
    include 'chatbot.php';
    ?>
    <script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
    $(document).ready(function(){
        AOS.init({
            duration: 400,
            offset: 100,
            once: true,
            easing: 'ease',
            anchorPlacement: 'top-bottom'
        });
        if ($('.bx-news-slider').length) {
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
                touchEnabled: false
            });
        }
    });
    </script>
</body>
</html>