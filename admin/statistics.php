<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra vai trò admin
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'phonedb');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Lấy dữ liệu cho biểu đồ doanh thu theo ngày (chỉ tính đơn hàng đã giao)
$sql_revenue = "SELECT DATE(order_date) as order_day, SUM(total_amount) as daily_revenue 
                FROM tbl_orders 
                WHERE status = 'delivered' 
                GROUP BY DATE(order_date) 
                ORDER BY order_day ASC";
$result_revenue = $conn->query($sql_revenue);

$revenue_labels = [];
$revenue_data = [];
while ($row = $result_revenue->fetch_assoc()) {
    $revenue_labels[] = date('d/m/Y', strtotime($row['order_day']));
    $revenue_data[] = floatval($row['daily_revenue']);
}

// Lấy tổng số đơn hàng và số đơn hàng đã giao
$sql_total_orders = "SELECT COUNT(*) as total FROM tbl_orders";
$result_total_orders = $conn->query($sql_total_orders);
$total_orders = $result_total_orders->fetch_assoc()['total'] ?? 0;

$sql_delivered_orders = "SELECT COUNT(*) as delivered FROM tbl_orders WHERE status = 'delivered'";
$result_delivered_orders = $conn->query($sql_delivered_orders);
$delivered_orders = $result_delivered_orders->fetch_assoc()['delivered'] ?? 0;

// Tính tổng doanh thu từ các đơn hàng đã giao
$sql_total_revenue = "SELECT SUM(total_amount) as total_revenue 
                      FROM tbl_orders 
                      WHERE status = 'delivered'";
$result_total_revenue = $conn->query($sql_total_revenue);
$total_revenue = $result_total_revenue->fetch_assoc()['total_revenue'] ?? 0;

// Dữ liệu cho biểu đồ tròn
$pie_labels = ['Đã giao', 'Chưa giao'];
$pie_data = [$delivered_orders, $total_orders - $delivered_orders];

// Lấy doanh thu theo hãng điện thoại (dựa trên tbl_categories và tbl_product_specifications)
$sql_revenue_by_category = "SELECT c.category_name, SUM(od.quantity * od.unit_price) as category_revenue
                            FROM tbl_orders o
                            JOIN tbl_order_details od ON o.order_id = od.order_id
                            JOIN tbl_products p ON od.product_id = p.product_id
                            JOIN tbl_categories c ON p.category_id = c.category_id
                            WHERE o.status = 'delivered'
                            GROUP BY c.category_id, c.category_name
                            ORDER BY category_revenue DESC";
$result_revenue_by_category = $conn->query($sql_revenue_by_category);

$category_labels = [];
$category_data = [];
$category_details = [];
while ($row = $result_revenue_by_category->fetch_assoc()) {
    $category_labels[] = $row['category_name'];
    $category_data[] = floatval($row['category_revenue']);
    $category_details[] = [
        'name' => $row['category_name'],
        'revenue' => floatval($row['category_revenue'])
    ];
}

// Lấy số lượng ảnh đã lưu
$sql_total_images = "SELECT COUNT(*) as total_images FROM tbl_product_images";
$result_total_images = $conn->query($sql_total_images);
$total_images = $result_total_images->fetch_assoc()['total_images'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Đơn Hàng và Doanh Thu</title>
    <style>
        * {
            box-sizing: border-box;
        }
     
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 2rem;
            gap: 1rem;
        }
        .stat-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1;
        }
        .stat-card h3 {
            font-size: 1.25rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        .charts {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
        }
        .chart-container {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        .category-details {
            margin-top: 2rem;
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .category-details h3 {
            font-size: 1.25rem;
            color: #374151;
            margin-bottom: 1rem;
        }
        .category-details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .category-details li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .category-details li:last-child {
            border-bottom: none;
        }
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .header {
                font-size: 1.5rem;
            }
            .stats {
                flex-direction: column;
            }
            .chart-container {
                max-width: 100%;
            }
            .category-details h3 {
                font-size: 1rem;
            }
            .category-details li {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">Thống Kê Đơn Hàng và Doanh Thu</h1>

        <!-- Thống kê số liệu -->
        <div class="stats">
            <div class="stat-card">
                <h3>Tổng Doanh Thu (Đã Giao)</h3>
                <p><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
            </div>
            <div class="stat-card">
                <h3>Tổng Đơn Hàng Đã Giao</h3>
                <p><?php echo $delivered_orders; ?> / <?php echo $total_orders; ?></p>
            </div>
            <div class="stat-card">
                <h3>Tổng Số Ảnh Sản Phẩm</h3>
                <p><?php echo $total_images; ?></p>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="charts">
            <!-- Biểu đồ cột: Doanh thu theo ngày -->
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Biểu đồ tròn: Tỷ lệ đơn hàng đã giao -->
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>

            <!-- Biểu đồ cột: Doanh thu theo hãng điện thoại -->
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Chi tiết doanh thu theo hãng -->
        <div class="category-details">
            <h3>Chi Tiết Doanh Thu Theo Hãng Điện Thoại</h3>
            <ul>
                <?php
                foreach ($category_details as $category) {
                    echo '<li>';
                    echo '<span>' . htmlspecialchars($category['name']) . '</span>';
                    echo '<span>' . number_format($category['revenue'], 0, ',', '.') . ' VNĐ</span>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- Thư viện Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ cột: Doanh thu theo ngày
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($revenue_labels); ?>,
                datasets: [{
                    label: 'Doanh Thu (VNĐ)',
                    data: <?php echo json_encode($revenue_data); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Doanh Thu (VNĐ)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Ngày'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Doanh Thu Theo Ngày (Đã Giao)'
                    }
                }
            }
        });

        // Biểu đồ tròn: Tỷ lệ đơn hàng đã giao
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($pie_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pie_data); ?>,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.6)', // Màu xanh cho "Đã giao"
                        'rgba(239, 68, 68, 0.6)'  // Màu đỏ cho "Chưa giao"
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tỷ Lệ Đơn Hàng Đã Giao'
                    }
                }
            }
        });

        // Biểu đồ cột: Doanh thu theo hãng điện thoại
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    label: 'Doanh Thu (VNĐ)',
                    data: <?php echo json_encode($category_data); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)', // Màu cam
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Doanh Thu (VNĐ)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hãng Điện Thoại'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Doanh Thu Theo Hãng Điện Thoại (Đã Giao)'
                    }
                }
            }
        });
    </script>
</body>
</html>