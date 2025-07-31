<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Thêm Swiper.js CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .phone-store-header {
      display: flex;
      height: 550px;
      width: 100%;
    }

    .header-container {
      display: flex;
      width: 100%;
      margin: 0 auto;
    }

    .header-left {
      width: 70%;
      height: 530px;
      background: #000; /* Background màu đen */
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      overflow: hidden;
      border-radius: 10px;
      margin: 10px;
      position: relative;
    }

    /* Hiệu ứng background chung */
    .background-effects {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1; /* Đặt phía sau các thành phần khác */
      overflow: hidden;
    }

    /* Hiệu ứng tia đổi màu */
    .background-effects .beam {
      position: absolute;
      width: 100%;
      height: 20px;
      background: linear-gradient(90deg, rgba(255, 0, 0, 0.3), rgba(0, 255, 0, 0.3), rgba(0, 0, 255, 0.3));
      opacity: 0.5;
      animation: beamMove 10s linear infinite;
    }

    .background-effects .beam:nth-child(1) {
      top: 20%;
      animation-delay: 0s;
    }

    .background-effects .beam:nth-child(2) {
      top: 50%;
      animation-delay: 2s;
    }

    .background-effects .beam:nth-child(3) {
      top: 80%;
      animation-delay: 4s;
    }

    @keyframes beamMove {
      0% {
        transform: translateX(-100%);
        background: linear-gradient(90deg, rgba(255, 0, 0, 0.3), rgba(0, 255, 0, 0.3), rgba(0, 0, 255, 0.3));
      }
      50% {
        background: linear-gradient(90deg, rgba(0, 255, 0, 0.3), rgba(0, 0, 255, 0.3), rgba(255, 0, 0, 0.3));
      }
      100% {
        transform: translateX(100%);
        background: linear-gradient(90deg, rgba(0, 0, 255, 0.3), rgba(255, 0, 0, 0.3), rgba(0, 255, 0, 0.3));
      }
    }

    /* Hiệu ứng tuyết rơi */
    .background-effects .snowflake {
      position: absolute;
      background: rgba(255, 255, 255, 0.8);
      opacity: 0.7;
      animation: fall linear infinite;
    }

    .background-effects .snowflake:nth-child(4) {
      left: 10%;
      width: 10px;
      height: 10px;
      border-radius: 50%; /* Hình tròn */
      animation-duration: 8s;
      animation-delay: 0s;
    }

    .background-effects .snowflake:nth-child(5) {
      left: 30%;
      width: 8px;
      height: 8px;
      clip-path: polygon(50% 0%, 0% 100%, 100% 100%); /* Hình tam giác */
      animation-duration: 10s;
      animation-delay: 2s;
    }

    .background-effects .snowflake:nth-child(6) {
      left: 50%;
      width: 12px;
      height: 12px; /* Hình vuông */
      animation-duration: 12s;
      animation-delay: 4s;
    }

    .background-effects .snowflake:nth-child(7) {
      left: 70%;
      width: 10px;
      height: 10px;
      border-radius: 50%; /* Hình tròn */
      animation-duration: 9s;
      animation-delay: 1s;
    }

    .background-effects .snowflake:nth-child(8) {
      left: 90%;
      width: 8px;
      height: 8px;
      clip-path: polygon(50% 0%, 0% 100%, 100% 100%); /* Hình tam giác */
      animation-duration: 11s;
      animation-delay: 3s;
    }

    @keyframes fall {
      0% {
        transform: translateY(-100%) rotate(0deg);
        opacity: 0.7;
      }
      100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
      }
    }

    .header-right {
      width: 30%;
      height: 530px;
      display: flex;
      flex-direction: column;
      margin: 10px;
    }

    .header-promotions {
      height: 30%;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      overflow: hidden;
      background: #000; /* Background màu đen giống header-left */
      border-radius: 10px;
      margin-bottom: 5px;
      position: relative;
    }

    /* CSS cho video đơn lẻ */
    .header-video {
      height: 70%;
      overflow: hidden;
      border-radius: 10px;
      margin-top: 5px;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .header-video video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: none;
    }

    .header-video .video-controls {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 10;
      display: flex;
      justify-content: center;
      align-items: center;
      opacity: 0; /* Ẩn nút mặc định */
      transition: opacity 0.3s ease;
    }

    .header-video:hover .video-controls {
      opacity: 1; /* Hiển thị nút khi hover */
    }

    .header-video .video-controls button {
      background: rgba(0, 0, 0, 0.5);
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: background 0.3s;
    }

    .header-video .video-controls button:hover {
      background: rgba(0, 0, 0, 0.8);
    }

    .header-video .video-controls .play-pause-btn::before {
      content: '▶';
      font-size: 24px;
      color: #fff;
    }

    .header-video .video-controls .play-pause-btn.paused::before {
      content: '❚❚';
      font-size: 24px;
      color: #fff;
    }

    /* CSS cho slider trong header-left */
    .header-left .poster-slider-left321 {
      width: 100%;
      height: 100%;
      z-index: 2; /* Đặt trên background-effects */
    }

    .header-left .poster-slider-left321 .swiper-slide {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px;
      background: transparent; /* Đảm bảo không có background image riêng */
      height: 100%;
    }

    .header-left .poster-slider-left321 .swiper-slide .image-container {
      position: relative;
      width: 60%;
      height: 80%;
      margin-left: auto;
    }

    .header-left .poster-slider-left321 .swiper-slide img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      position: absolute;
      top: 0;
      left: 0;
      animation: float 3s ease-in-out infinite;
      opacity: 0; /* Ban đầu ẩn để chạy fadeIn */
      z-index: 3; /* Đặt trên background-effects */
    }

    .header-left .poster-slider-left321 .swiper-slide img.anh-img {
      animation: float 3s ease-in-out infinite, fadeIn 1s ease-in 0.2s forwards;
    }

    .header-left .poster-slider-left321 .swiper-slide img.thongso-img {
      animation: float 3s ease-in-out infinite, fadeIn 1s ease-in 0.5s forwards;
    }

    /* Class để kích hoạt lại fadeIn khi chuyển slide */
    .header-left .poster-slider-left321 .swiper-slide-active img.anh-img {
      animation: float 3s ease-in-out infinite, fadeIn 1s ease-in 0.2s forwards;
    }

    .header-left .poster-slider-left321 .swiper-slide-active img.thongso-img {
      animation: float 3s ease-in-out infinite, fadeIn 1s ease-in 0.5s forwards;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    @keyframes fadeIn {
      0% { opacity: 0; }
      100% { opacity: 1; }
    }

    .header-left .text-container {
      position: absolute;
      left: 80px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      max-width: 40%;
      z-index: 4; /* Đặt trên image-container */
    }

    .header-left .text-left {
      color: #ffffff;
      font-size: 36px;
      font-weight: bold;
      text-transform: uppercase;
      text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(30, 60, 114, 0.6), 0 0 30px rgba(142, 45, 226, 0.6);
      animation: slideInLeft 1s ease-out forwards;
      margin-bottom: 10px;
    }

    .header-left .buy-now-btn {
      padding: 10px 20px;
      background: linear-gradient(135deg, #007bff, #00c4ff); /* Gradient xanh dương */
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s, transform 0.1s, box-shadow 0.3s;
      text-shadow: 0 0 5px rgba(0, 0, 0, 0.5); /* Thêm text-shadow */
    }

    .header-left .buy-now-btn:hover {
      background: linear-gradient(135deg, #00c4ff, #007bff); /* Đảo ngược gradient */
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); /* Hiệu ứng sáng */
    }

    @keyframes slideInLeft {
      0% { transform: translateX(-100%); opacity: 0; }
      100% { transform: translateX(0); opacity: 1; }
    }

    .header-left .poster-pagination321 {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex
;
    background: #555454;
    width: 40px;
    height: 17px;
    border-radius: 10px;
    text-align: center;
    align-items: center;
    justify-content: center;
    }

    .header-left .poster-pagination321 .swiper-pagination-bullet {
      width: 12px;
      height: 12px;
      background: #fff;
      opacity: 0.5;
      transition: opacity 0.3s, background-color 0.3s;
    }

    .header-left .poster-pagination321 .swiper-pagination-bullet-active {
      opacity: 1;
      background:rgb(0, 0, 0);
    }

    /* CSS cho header-promotions */
    .header-promotions .promo-slider-right321 {
      width: 100%;
      height: 100%;
      padding: 10px;
      z-index: 2; /* Đặt trên background-effects */
    }

    .header-promotions .promo-slider-right321 .swiper-slide {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: -10px;
    }

    .header-promotions .promo-box {
      width: 90%;
      height: 90%;
      display: flex;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s, box-shadow 0.3s;
      z-index: 3; /* Đặt trên background-effects */
    }

    .header-promotions .promo-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    .header-promotions .promo-box img {
      width: 40%;
      height: 100%;
      object-fit: cover;
      border-radius: 10px 0 0 10px;
    }

    .header-promotions .promo-info {
      width: 60%;
      padding: 15px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      color: #fff;
    }

    .header-promotions .promo-info div:first-child {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 5px;
      color: #fff; /* Đổi màu chữ thành trắng để hợp với nền đen */
      text-transform: uppercase;
      text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }

    .header-promotions .promo-info .discount-value {
      font-size: 16px;
      margin-bottom: 5px;
    }

    .header-promotions .promo-info .end-date {
      font-size: 12px;
      opacity: 0.8;
      margin-bottom: 10px;
    }

    .header-promotions .promo-info .copy-btn {
      padding: 8px 15px;
      background: linear-gradient(135deg, #007bff, #00c4ff); /* Gradient xanh dương */
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s, transform 0.1s, box-shadow 0.3s;
      text-shadow: 0 0 5px rgba(0, 0, 0, 0.5); /* Thêm text-shadow */
    }

    .header-promotions .promo-info .copy-btn:hover {
      background: linear-gradient(135deg, #00c4ff, #007bff); /* Đảo ngược gradient */
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); /* Hiệu ứng sáng */
    }

    .header-promotions .promo-info .copy-btn.copied {
      background: linear-gradient(135deg, #ff9800, #ffb74d); /* Gradient vàng khi copied */
      transform: scale(1.1);
      box-shadow: 0 0 10px rgba(255, 152, 0, 0.5);
    }

    .header-promotions .promo-pagination321 {
      position: absolute;
      bottom: 2 px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      justify-content: center;
      background:#555454;
    width: 255px;
    align-items: center;
    height: 15px;
    border-radius: 10px;
    }

    .header-promotions .promo-pagination321 .swiper-pagination-bullet {
      width: 12px;
      height: 10px;
      background: #fff;
      opacity: 0.5;
      transition: opacity 0.3s, background-color 0.3s;
    }

    .header-promotions .promo-pagination321 .swiper-pagination-bullet-active {
      opacity: 1;
      background:rgb(0, 0, 0); /* Đổi màu bullet thành xanh dương */
    }

    /* Responsive */
    @media (max-width: 768px) {
      .phone-store-header {
        flex-direction: column;
        height: auto;
      }

      .header-container {
        flex-direction: column;
      }

      .header-left {
        width:auto;
        height: 400px; /* Giảm chiều cao trên mobile */
        margin: 5px 10px;
      }

      .header-right {
        width: 100%;
        height: auto;
        margin: 0 0 10px;
      }

      .header-promotions,
      .header-video {
        height: auto;
        margin: 5px 10px;
      }

      /* Responsive cho header-left: Giữ anh và thongso chồng lên nhau */
      .header-left .poster-slider-left321 .swiper-slide {
        padding: 20px 10px;
        position: relative;
      }

      .header-left .text-left {
        font-size: 13px;
      }

      .header-left .poster-slider-left321 .swiper-slide .image-container {
        width: 90%;
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        height:90%;
      }

      .header-left .poster-slider-left321 .swiper-slide img {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
      }

      .header-left .text-container {
        position: absolute;
        left: 50%;
        top: 80%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        align-items: center;
        text-align: center;
        padding: 0 20px;
      }

      .header-left .buy-now-btn {
        width: 100%;
        max-width: 200px;
        margin: 10px auto;
      }

      .header-left .poster-pagination321 {
        bottom: 10px;
      }

      .header-left .poster-pagination321 .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
      }

      /* Responsive cho header-promotions: Slide nằm ngang */
      .header-promotions .promo-slider-right321 {
        width: 100%;
        height: 100%;
      }

      .header-promotions .promo-slider-right321 .swiper-wrapper {
        display: flex;
        flex-direction: row;
      }

      .header-promotions .promo-slider-right321 .swiper-slide {
        width: 380px; /* Đặt chiều rộng cố định cho slide */
        height: auto;
        padding: 10px;
        
      }

      .header-promotions .promo-box {
        flex-direction: row;
        width: 100%;
        height: 100%;
      }

      .header-promotions .promo-box img {
        width: 40%;
        height: 100%;
        border-radius: 10px 0 0 10px;
      }

      .header-promotions .promo-info {
        width: 60%;
        text-align: left;
      }

      .header-promotions .promo-pagination321 {
        bottom: 10px;
      }

      .header-promotions .promo-pagination321 .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
      }

      .header-video video {
        height: 200px; /* Điều chỉnh chiều cao trên mobile */
      }
    }
  </style>
</head>
<body>
  <header class="phone-store-header">
    <div class="header-container">
      <!-- Phần bên trái: Slider poster -->
      <div class="header-left">
        <!-- Hiệu ứng background -->
        <div class="background-effects">
          <div class="beam"></div>
          <div class="beam"></div>
          <div class="beam"></div>
          <div class="snowflake"></div>
          <div class="snowflake"></div>
          <div class="snowflake"></div>
          <div class="snowflake"></div>
          <div class="snowflake"></div>
        </div>
        <div class="poster-slider-left321">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="text-container">
                <div class="text-left">IPHONE 16 PRO MAX</div>
                <button class="buy-now-btn" onclick="scrollToMainContainer()">Mua ngay</button>
              </div>
              <div class="image-container">
                <img src="public/images/anh.png" alt="iPhone 16 Pro Max" class="anh-img">
                <img src="public/images/thongso.png" alt="Thông số" class="thongso-img">
              </div>
            </div>
            <div class="swiper-slide">
              <div class="text-container">
                <div class="text-left">GALAXY S25 ULTRA</div>
                <button class="buy-now-btn" onclick="scrollToMainContainer()">Mua ngay</button>
              </div>
              <div class="image-container">
                <img src="public/images/anh1.png" alt="Samsung Galaxy S25 Ultra" class="anh-img">
                <img src="public/images/thongso1.png" alt="Thông số" class="thongso-img">
              </div>
            </div>
          </div>
          <div class="poster-pagination321"></div>
        </div>
      </div>

      <!-- Phần bên phải -->
      <div class="header-right">
        <!-- Phần trên: Slider mã giảm giá -->
        <div class="header-promotions">
          <!-- Hiệu ứng background giống header-left -->
          <div class="background-effects">
            <div class="beam"></div>
            <div class="beam"></div>
            <div class="beam"></div>
            <div class="snowflake"></div>
            <div class="snowflake"></div>
            <div class="snowflake"></div>
            <div class="snowflake"></div>
            <div class="snowflake"></div>
          </div>
          <?php
          $conn = new mysqli("localhost", "root", "", "phonedb");
          if ($conn->connect_error) {
              die("Kết nối thất bại: " . $conn->connect_error);
          }

          $sql_promotions = "SELECT promo_code, discount_value, discount_type, end_date
                             FROM tbl_promotions
                             WHERE status = 'active' AND end_date >= CURDATE()";
          $result_promotions = $conn->query($sql_promotions);

          echo '<div class="promo-slider-right321">';
          echo '<div class="swiper-wrapper">';
          if ($result_promotions->num_rows > 0) {
            while ($row = $result_promotions->fetch_assoc()) {
              $discount = $row['discount_type'] == 'percentage' ? $row['discount_value'] . '%' : number_format($row['discount_value'], 0, ',', '.') . ' VNĐ';
              echo '<div class="swiper-slide">';
              echo '<div class="promo-box">';
              echo '<img src="public/images/discount.png" alt="Promotion">';
              echo '<div class="promo-info">';
              echo '<h3>' . htmlspecialchars($row['promo_code']) . '</h3>';
              echo '<p class="discount-value">Giảm ' . $discount . '</p>';
              echo '<p class="end-date">Hết hạn: ' . date('d/m/Y', strtotime($row['end_date'])) . '</p>';
              echo '<button class="copy-btn" onclick="copyPromoCode(\'' . addslashes($row['promo_code']) . '\', this)">Copy</button>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }
          } else {
            echo '<div class="swiper-slide">';
            echo '<div class="promo-box">';
            echo '<img src="public/images/discount.png" alt="Promotion">';
            echo '<div class="promo-info">';
            echo '<h3>Không có mã giảm giá</h3>';
            echo '<p class="discount-value">Hãy quay lại sau!</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
          }
          echo '</div>';
          echo '<div class="promo-pagination321"></div>';
          echo '</div>';

          $conn->close();
          ?>
        </div>

        <!-- Phần dưới: Video đơn lẻ -->
        <div class="header-video">
          <video id="trailerVideo" muted loop playsinline autoplay>
            <source src="public/images/trailer.mp4" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          <div class="video-controls">
            <button id="playPauseBtn" class="play-pause-btn" onclick="togglePlayPause()"></button>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="main-container">
    <!-- Nội dung chính sẽ được thêm ở đây -->
  </div>

  <!-- Khởi tạo Swiper và JavaScript -->
  <script>
    // Slider cho poster
    const posterSwiper = new Swiper('.poster-slider-left321', {
      loop: true,
      autoplay: { delay: 5000 },
      pagination: { el: '.poster-pagination321', clickable: true, type: 'bullets' },
      on: {
        init: function () {
          // Đảm bảo slide đầu tiên hiển thị đúng
          const activeSlide = document.querySelector('.poster-slider-left321 .swiper-slide-active');
          if (activeSlide) {
            const anhImg = activeSlide.querySelector('.anh-img');
            const thongsoImg = activeSlide.querySelector('.thongso-img');
            if (anhImg) anhImg.style.animation = 'float 3s ease-in-out infinite, fadeIn 1s ease-in 0.2s forwards';
            if (thongsoImg) thongsoImg.style.animation = 'float 3s ease-in-out infinite, fadeIn 1s ease-in 0.5s forwards';
          }
        },
        slideChange: function () {
          const activeSlide = document.querySelector('.poster-slider-left321 .swiper-slide-active');
          if (!activeSlide) return;

          document.querySelectorAll('.poster-slider-left321 .swiper-slide img').forEach(img => {
            img.style.opacity = '0';
          });

          const anhImg = activeSlide.querySelector('.anh-img');
          const thongsoImg = activeSlide.querySelector('.thongso-img');
          if (anhImg) anhImg.style.animation = 'float 3s ease-in-out infinite, fadeIn 1s ease-in 0.2s forwards';
          if (thongsoImg) thongsoImg.style.animation = 'float 3s ease-in-out infinite, fadeIn 1s ease-in 0.5s forwards';
        },
      },
    });

    // Slider cho mã giảm giá
    const promoSwiper = new Swiper('.promo-slider-right321', {
      loop: true,
      autoplay: { delay: 3000, disableOnInteraction: false },
      pagination: { el: '.promo-pagination321', clickable: true, type: 'bullets' },
      slidesPerView: 1,
      spaceBetween: 10,
      on: {
        init: function () {
          if (this.slides.length <= 1) {
            this.autoplay.stop();
          }
        },
      },
      breakpoints: {
        768: {
          slidesPerView: 1,
          spaceBetween: 10,
        },
        0: {
          slidesPerView: 'auto', // Cho phép slide nằm ngang trên mobile
          spaceBetween: 10,
        },
      },
    });

    // Điều khiển video
    const trailerVideo = document.getElementById('trailerVideo');
    const playPauseBtn = document.getElementById('playPauseBtn');

    function togglePlayPause() {
      if (trailerVideo.paused) {
        trailerVideo.play();
        playPauseBtn.classList.remove('paused');
      } else {
        trailerVideo.pause();
        playPauseBtn.classList.add('paused');
      }
    }

    // Chức năng sao chép mã giảm giá
    function copyPromoCode(code, button) {
      navigator.clipboard.writeText(code).then(() => {
        button.classList.add('copied');
        button.textContent = 'Đã copy!';
        setTimeout(() => {
          button.classList.remove('copied');
          button.textContent = 'Copy';
        }, 2000);
      }).catch(err => {
        console.error('Lỗi khi sao chép mã: ', err);
      });
    }

    // Chức năng cuộn đến main-container
    function scrollToMainContainer() {
      const productList = document.querySelector('.product-list');
      if (productList) {
        productList.scrollIntoView({ behavior: 'smooth' });
      }
    }
  </script>
</body>
</html>