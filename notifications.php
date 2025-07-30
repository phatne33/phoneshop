<?php
session_start();
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Xử lý yêu cầu AJAX
if (isset($_GET['action']) && $_GET['action'] === 'fetch_notifications') {
    header('Content-Type: application/json');

    // Xử lý đánh dấu đã đọc
    if (isset($_POST['mark_read']) && is_numeric($_POST['mark_read'])) {
        $notification_id = (int)$_POST['mark_read'];
        $stmt = $conn->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Hàm lấy thông báo
    $notifications = [];

    // 1. Sản phẩm trong giỏ hàng có flashsale
    $query = "
        SELECT c.product_id, p.product_name, f.flashsale_id, fs.title, fs.end_time, fs.start_time
        FROM tbl_cart c
        JOIN tbl_flashsale f ON c.product_id = f.product_id
        JOIN tbl_flashsales fs ON f.flashsale_id = fs.flashsale_id
        JOIN tbl_products p ON c.product_id = p.product_id
        WHERE c.user_id = ? AND fs.status = 'active' AND fs.start_time <= NOW() AND fs.end_time >= NOW()
        ORDER BY fs.start_time DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'cart_flashsale',
            'message' => "Sản phẩm <strong>{$row['product_name']}</strong> trong giỏ hàng của bạn đang có flashsale <strong>{$row['title']}</strong>! Kết thúc vào " . date('d/m/Y H:i', strtotime($row['end_time'])),
            'time' => date('d/m/Y H:i', strtotime($row['start_time'])),
            'link' => "flashsale.php?id={$row['flashsale_id']}",
            'is_read' => 0,
            'notification_id' => 'cart_' . $row['flashsale_id'] . '_' . $row['product_id']
        ];
    }
    $stmt->close();

    // 2. Sản phẩm yêu thích có flashsale
    $query = "
        SELECT f.product_id, p.product_name, fs.flashsale_id, fs.title, fs.end_time, fs.start_time
        FROM tbl_favorites f
        JOIN tbl_flashsale fsale ON f.product_id = fsale.product_id
        JOIN tbl_flashsales fs ON fsale.flashsale_id = fs.flashsale_id
        JOIN tbl_products p ON f.product_id = p.product_id
        WHERE f.user_id = ? AND fs.status = 'active' AND fs.start_time <= NOW() AND fs.end_time >= NOW()
        ORDER BY fs.start_time DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'favorite_flashsale',
            'message' => "Sản phẩm yêu thích <strong>{$row['product_name']}</strong> đang có flashsale <strong>{$row['title']}</strong>! Kết thúc vào " . date('d/m/Y H:i', strtotime($row['end_time'])),
            'time' => date('d/m/Y H:i', strtotime($row['start_time'])),
            'link' => "flashsale.php?id={$row['flashsale_id']}",
            'is_read' => 0,
            'notification_id' => 'fav_' . $row['flashsale_id'] . '_' . $row['product_id']
        ];
    }
    $stmt->close();

    // 3. Bình luận được admin trả lời
    $query = "
        SELECT r.review_id, r.product_id, r.comment, r.created_at, r.admin_reply, p.product_name
        FROM tbl_reviews r
        JOIN tbl_products p ON r.product_id = p.product_id
        WHERE r.user_id = ? AND r.admin_reply != ''
        ORDER BY r.created_at DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'review_reply',
            'message' => "Bình luận của bạn về sản phẩm <strong>{$row['product_name']}</strong> đã được admin trả lời: \"{$row['admin_reply']}\"",
            'time' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'link' => "product.php?id={$row['product_id']}#review-{$row['review_id']}",
            'is_read' => 0,
            'notification_id' => 'review_' . $row['review_id']
        ];
    }
    $stmt->close();

    // 4. Sản phẩm mới được thêm (trong 3 ngày gần đây)
    $query = "
        SELECT product_id, product_name, created_at
        FROM tbl_products
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY created_at DESC
        LIMIT 5
    ";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'new_product',
            'message' => "Sản phẩm mới <strong>{$row['product_name']}</strong> vừa được thêm vào cửa hàng!",
            'time' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'link' => "product.php?id={$row['product_id']}",
            'is_read' => 0,
            'notification_id' => 'product_' . $row['product_id']
        ];
    }

    // 5. Lấy thông báo từ tbl_notifications
    $query = "
        SELECT notification_id, type, message, link, is_read, created_at
        FROM tbl_notifications
        WHERE user_id = ?
        ORDER BY is_read ASC, created_at DESC
        LIMIT 20
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => $row['type'],
            'message' => $row['message'],
            'time' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'link' => $row['link'],
            'is_read' => (int)$row['is_read'],
            'notification_id' => $row['notification_id']
        ];
    }
    $stmt->close();

    // Sắp xếp theo thời gian
    usort($notifications, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Lấy số thông báo chưa đọc
    $query = "SELECT COUNT(*) as unread_count FROM tbl_notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_count = $result->fetch_assoc()['unread_count'];
    $stmt->close();

    // Trả về JSON
    echo json_encode([
        'notifications' => array_slice($notifications, 0, 20),
        'unread_count' => $unread_count
    ]);
    exit();
}

// Lấy số thông báo chưa đọc ban đầu
$query = "SELECT COUNT(*) as unread_count FROM tbl_notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_count = $result->fetch_assoc()['unread_count'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .unread-count {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .notification {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .unread {
            background-color: #e6f3ff;
            border-left: 4px solid #007bff;
        }
        .read {
            background-color: #f8f8f8;
            border-left: 4px solid #ccc;
        }
        .notification p {
            margin: 0 0 10px 0;
            color: #333;
            line-height: 1.5;
        }
        .notification a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .notification a:hover {
            text-decoration: underline;
        }
        .mark-read {
            font-size: 0.9em;
            color: #555;
            cursor: pointer;
        }
        .mark-read:hover {
            color: #007bff;
        }
        .time {
            font-size: 0.85em;
            color: #666;
        }
        .no-notifications {
            text-align: center;
            color: #666;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .notification {
                padding: 10px;
            }
            h2 {
                font-size: 1.5em;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Hàm lấy thông báo
            function fetchNotifications() {
                $.ajax({
                    url: 'notifications.php?action=fetch_notifications',
                    method: 'GET',
                    data: { user_id: <?php echo $user_id; ?> },
                    dataType: 'json',
                    success: function(data) {
                        $('#notification-list').empty();
                        if (data.notifications.length === 0) {
                            $('#notification-list').append('<p class="no-notifications">Hiện tại không có thông báo nào.</p>');
                        } else {
                            $.each(data.notifications, function(index, notif) {
                                var isDynamic = ['cart_flashsale', 'favorite_flashsale', 'review_reply', 'new_product'].includes(notif.type);
                                var notificationHtml = `
                                    <div class="notification ${notif.is_read ? 'read' : 'unread'}" data-id="${notif.notification_id}">
                                        <p>${notif.message}</p>
                                        ${notif.link ? `<a href="${notif.link}" class="view-detail" ${isDynamic ? '' : `data-id="${notif.notification_id}"`}>Xem chi tiết</a>` : ''}
                                        ${!notif.is_read && !isDynamic ? `<a class="mark-read" data-id="${notif.notification_id}">Đánh dấu đã đọc</a>` : ''}
                                        <p class="time">${notif.time}</p>
                                    </div>`;
                                $('#notification-list').append(notificationHtml);
                            });
                        }
                        $('#unread-count').text(data.unread_count > 0 ? `${data.unread_count} chưa đọc` : '');
                    },
                    error: function() {
                        $('#notification-list').html('<p class="no-notifications">Lỗi khi tải thông báo.</p>');
                    }
                });
            }

            // Gọi fetchNotifications lần đầu
            fetchNotifications();

            // Cập nhật thông báo mỗi 10 giây
            setInterval(fetchNotifications, 10000);

            // Xử lý đánh dấu đã đọc khi nhấp "Đánh dấu đã đọc"
            $(document).on('click', '.mark-read', function() {
                var notificationId = $(this).data('id');
                markAsRead(notificationId, $(this).closest('.notification'));
            });

            // Xử lý đánh dấu đã đọc khi nhấp "Xem chi tiết"
            $(document).on('click', '.view-detail', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                var url = $(this).attr('href');
                if (notificationId) {
                    markAsRead(notificationId, $(this).closest('.notification'), function() {
                        window.open(url, '_blank');
                    });
                } else {
                    window.open(url, '_blank');
                }
            });

            // Hàm đánh dấu đã đọc
            function markAsRead(notificationId, $notification, callback) {
                $.ajax({
                    url: 'notifications.php?action=fetch_notifications',
                    method: 'POST',
                    data: { mark_read: notificationId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $notification.removeClass('unread').addClass('read');
                            $notification.find('.mark-read').remove();
                            $('#unread-count').text(response.unread_count > 0 ? `${response.unread_count} chưa đọc` : '');
                            if (callback) callback();
                        }
                    }
                });
            }
        });
    </script>
</head>
<body>
    <h2>
        Thông báo
        <span id="unread-count" class="unread-count"><?php echo $unread_count > 0 ? "$unread_count chưa đọc" : ''; ?></span>
    </h2>
    <div id="notification-list">
        <!-- Thông báo sẽ được thêm động bằng AJAX -->
    </div>
</body>
</html>
<?php $conn->close(); ?>