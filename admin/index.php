<?php
// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'phonedb');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Xử lý logic trước khi xuất HTML
$page = $_GET['page'] ?? 'statistics';
if ($page === "categories") {
    include("category_manage.php");
    // Thoát nếu có chuyển hướng từ category_manage.php
    if (isset($redirect)) {
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/admin.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 1rem;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar h4 {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 0.5rem;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: block;
            border-radius: 5px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #007bff;
        }

        /* Content styles */
        .content {
            margin-left: 250px;
            padding: 2rem;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        header {
            background-color: #f8f9fa;
            padding: 1rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Hamburger menu */
        .hamburger {
            display: none;
            font-size: 1.5rem;
            background: none;
            border: none;
            color: #343a40;
            cursor: pointer;
            padding: 0.5rem;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                width: 100%;
            }

            .hamburger {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger menu button -->
    <button class="hamburger" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h4>Admin Panel</h4>
        <ul>
            <li><a href="?page=statistics" class="<?php echo (!isset($_GET['page']) || $_GET['page'] === 'statistics') ? 'active' : ''; ?>">Quản lý doanh thu</a></li>
            <li><a href="?page=categories" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'categories') ? 'active' : ''; ?>">Quản lý danh mục</a></li>
            <li><a href="?page=products" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'products') ? 'active' : ''; ?>">Quản lý sản phẩm</a></li>
            <li><a href="?page=orderlist" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'orderlist') ? 'active' : ''; ?>">Quản lý đơn hàng</a></li>
            <li><a href="?page=promotions" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'promotions') ? 'active' : ''; ?>">Quản lý mã giảm giá</a></li>
            <li><a href="?page=flashsale" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'flashsale') ? 'active' : ''; ?>">Quản lý Flashsales</a></li>
            <li><a href="?page=reviews" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'reviews') ? 'active' : ''; ?>">Quản lý Đánh giá</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">
        <header>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </header>

        <main>
            <?php
            // Chỉ hiển thị nội dung nếu không ở trang categories
            if ($page === "products") {
                include("product_manage.php");
            } elseif ($page === "orderlist") {
                include("orderlist.php");
            } elseif ($page === "statistics") {
                include("statistics.php");
            } elseif ($page === "promotions") {
                include("quanlypromotions.php");
            } elseif ($page === "addnew") {
                include("add_news.php");
            } elseif ($page === "flashsale") {
                include("flashsale.php");
            } elseif ($page === "reviews") {
                include("admin_reviews.php");
            } elseif ($page !== "categories") {
                include("statistics.php");
            }
            ?>
        </main>
    </div>

    <!-- JavaScript for toggling sidebar -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.querySelector('.hamburger');
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnHamburger = hamburger.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnHamburger && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>