<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? 5; // Giá trị mặc định là 5 nếu session không có

// Khởi tạo kết nối cơ sở dữ liệu trong file này
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM tbl_addresses WHERE address_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($sql);
    if ($delete_stmt) {
        $delete_stmt->bind_param("ii", $delete_id, $user_id);
        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            header("Location: index.php?page=addresses"); // Chuyển hướng sau khi xóa
            exit();
        } else {
            $error_message = "Lỗi khi xóa: " . $conn->error;
            $delete_stmt->close();
        }
    } else {
        $error_message = "Lỗi chuẩn bị truy vấn xóa: " . $conn->error;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isModal = isset($_POST['isModal']) && $_POST['isModal'] == 1 ? true : false;

    $address_id = $_POST['address_id'] ?? null;
    $province = $_POST['province'] ?? '';
    $district = $_POST['district'] ?? '';
    $commune = $_POST['commune'] ?? '';
    $hamlet = $_POST['hamlet'] ?? '';
    $address_detail = $_POST['address_detail'] ?? '';
    $recipient_phone = $_POST['recipient_phone'] ?? '';
    $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;

    if ($address_id) {
        // Cập nhật địa chỉ
        $sql = "UPDATE tbl_addresses SET recipient_phone = ?, province = ?, district = ?, commune = ?, hamlet = ?, address_detail = ?, latitude = ?, longitude = ? WHERE address_id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($sql);
        if ($update_stmt) {
            $update_stmt->bind_param("sssssssddi", $recipient_phone, $province, $district, $commune, $hamlet, $address_detail, $latitude, $longitude, $address_id, $user_id);
            if ($update_stmt->execute()) {
                $update_stmt->close();
                // Trả về script để quay lại trang trước
                echo '<script>';
                if ($isModal) {
                    echo 'if (window.parent && window.parent.document) {';
                    echo '  const modal = window.parent.document.getElementById("addressModal");';
                    echo '  if (modal) modal.style.display = "none";'; // Đóng modal
                    echo '}';
                }
                echo 'window.history.back();'; // Quay lại trang trước ngay lập tức
                echo '</script>';
                exit();
            } else {
                $error_message = "Lỗi khi cập nhật: " . $conn->error;
                $update_stmt->close();
            }
        }
    } else {
        // Thêm địa chỉ mới
        $check_sql = "SELECT address_id FROM tbl_addresses WHERE user_id = ? AND province = ? AND district = ? AND commune = ? AND hamlet = ? AND address_detail = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("isssss", $user_id, $province, $district, $commune, $hamlet, $address_detail);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $check_stmt->close();
                header("Location: index.php?page=addresses&error=1");
                exit();
            } else {
                $sql = "INSERT INTO tbl_addresses (user_id, recipient_name, recipient_phone, province, district, commune, hamlet, address_detail, latitude, longitude, is_default) 
                        VALUES (?, 'Người Nhận', ?, ?, ?, ?, ?, ?, ?, ?, 0)";
                $insert_stmt = $conn->prepare($sql);
                if ($insert_stmt) {
                    $insert_stmt->bind_param("issssssdd", $user_id, $recipient_phone, $province, $district, $commune, $hamlet, $address_detail, $latitude, $longitude);
                    if ($insert_stmt->execute()) {
                        $insert_stmt->close();
                        // Trả về script để quay lại trang trước
                        echo '<script>';
                        if ($isModal) {
                            echo 'if (window.parent && window.parent.document) {';
                            echo '  const modal = window.parent.document.getElementById("addressModal");';
                            echo '  if (modal) modal.style.display = "none";'; // Đóng modal
                            echo '}';
                        }
                        echo 'window.location.href = "orders.php";
'; // Quay lại trang trước ngay lập tức
                        echo '</script>';
                        exit();
                    } else {
                        $error_message = "Lỗi: " . $conn->error;
                        $insert_stmt->close();
                    }
                }
            }
            $check_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Địa Chỉ</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Không cần Toastify nữa vì không hiển thị thông báo -->
    <style>
        /* Giữ nguyên style của bạn */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .address-list {
            margin-bottom: 30px;
        }
        .address-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #fafafa;
        }
        .address-item p {
            margin: 5px 0;
            color: #555;
        }
        .form-container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        button {
            background: #007bff;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .exit-btn {
            background: #6c757d;
        }
        .exit-btn:hover {
            background: #5a6268;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none; /* Ẩn bản đồ mặc định */
        }
        .success-message {
            color: #28a745;
            margin-top: 10px;
        }
        .action-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .edit-btn {
            background: #ffc107;
        }
        .edit-btn:hover {
            background: #e0a800;
        }
        .delete-btn {
            background: #dc3545;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .map-toggle-btn {
            background: #28a745;
            margin-top: 10px;
        }
        .map-toggle-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản Lý Địa Chỉ</h2>

        <!-- Danh sách địa chỉ -->
        <div class="address-list">
            <?php
            $sql = "SELECT address_id, province, district, commune, hamlet, address_detail, recipient_phone, latitude, longitude FROM tbl_addresses WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $province_name = $row['province'];
                    $district_name = $row['district'];
                    $commune_name = $row['commune'];

                    if ($row['province']) {
                        $province_data = @file_get_contents("https://provinces.open-api.vn/api/p/{$row['province']}");
                        if ($province_data !== false) {
                            $province_json = json_decode($province_data, true);
                            $province_name = $province_json['name'] ?? $row['province'];
                        }
                    }

                    if ($row['district']) {
                        $district_data = @file_get_contents("https://provinces.open-api.vn/api/d/{$row['district']}");
                        if ($district_data !== false) {
                            $district_json = json_decode($district_data, true);
                            $district_name = $district_json['name'] ?? $row['district'];
                        }
                    }

                    if ($row['commune']) {
                        $commune_data = @file_get_contents("https://provinces.open-api.vn/api/w/{$row['commune']}");
                        if ($commune_data !== false) {
                            $commune_json = json_decode($commune_data, true);
                            $commune_name = $commune_json['name'] ?? $row['commune'];
                        }
                    }

                    $full_address = htmlspecialchars($row['address_detail']) . ', ' . 
                                    htmlspecialchars($row['hamlet']) . ', ' . 
                                    htmlspecialchars($commune_name) . ', ' . 
                                    htmlspecialchars($district_name) . ', ' . 
                                    htmlspecialchars($province_name);

                    echo '<div class="address-item">';
                    echo '<p><strong>Địa chỉ:</strong> ' . $full_address . '</p>';
                    echo '<p><strong>Số điện thoại:</strong> ' . htmlspecialchars($row['recipient_phone']) . '</p>';
                    if ($row['latitude'] && $row['longitude']) {
                        echo '<p><strong>Tọa độ:</strong> Vĩ độ: ' . htmlspecialchars($row['latitude']) . ', Kinh độ: ' . htmlspecialchars($row['longitude']) . '</p>';
                    }
                    echo '<div class="action-buttons">';
                    echo '<button class="edit-btn" onclick="editAddress(' . $row['address_id'] . ', \'' . htmlspecialchars($row['province']) . '\', \'' . htmlspecialchars($row['district']) . '\', \'' . htmlspecialchars($row['commune']) . '\', \'' . htmlspecialchars($row['hamlet']) . '\', \'' . htmlspecialchars($row['address_detail']) . '\', \'' . htmlspecialchars($row['recipient_phone']) . '\', ' . ($row['latitude'] ?? 'null') . ', ' . ($row['longitude'] ?? 'null') . ')">Sửa</button>';
                    echo '<button class="delete-btn" onclick="deleteAddress(' . $row['address_id'] . ')">Xóa</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Chưa có địa chỉ nào được lưu.</p>';
            }
            $stmt->close();
            ?>
        </div>

        <!-- Form thêm/cập nhật địa chỉ -->
        <div class="form-container">
            <h2 id="form-title">Thêm Địa Chỉ Mới</h2>
            <form id="addressForm" method="POST" action="">
                <input type="hidden" id="address_id" name="address_id">
                <input type="hidden" id="isModal" name="isModal" value="<?php echo isset($_GET['isModal']) && $_GET['isModal'] == 1 ? 1 : 0; ?>">
                <div class="form-group">
                    <label for="province">Tỉnh/Thành phố:</label>
                    <select id="province" name="province" required>
                        <option value="">Chọn tỉnh/thành phố</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="district">Huyện/Quận:</label>
                    <select id="district" name="district" required>
                        <option value="">Chọn huyện/quận</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="commune">Xã/Phường:</label>
                    <select id="commune" name="commune" required>
                        <option value="">Chọn xã/phường</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hamlet">Ấp/Xóm/Thôn:</label>
                    <input type="text" id="hamlet" name="hamlet" placeholder="Nhập tên ấp/thôn/xóm">
                </div>
                <div class="form-group">
                    <label for="address_detail">Địa chỉ chi tiết:</label>
                    <input type="text" id="address_detail" name="address_detail" required>
                </div>
                <div class="form-group">
                    <label for="recipient_phone">Số điện thoại:</label>
                    <input type="text" id="recipient_phone" name="recipient_phone" pattern="[0-9]{10}" required>
                </div>
                <div class="form-group">
                    <label for="latitude">Vĩ độ (Latitude):</label>
                    <input type="number" step="any" id="latitude" name="latitude" readonly>
                </div>
                <div class="form-group">
                    <label for="longitude">Kinh độ (Longitude):</label>
                    <input type="number" step="any" id="longitude" name="longitude" readonly>
                </div>
                <div class="button-group">
                    <button type="submit" id="submit-btn">Lưu Địa Chỉ</button>
                    <button type="button" class="exit-btn" onclick="window.history.back()">Thoát</button>
                </div>
            </form>
            <button id="toggleMapBtn" class="map-toggle-btn">Thêm vị trí trên bản đồ</button>
            <div id="map"></div>
        </div>

        <!-- Thông báo lỗi nếu có -->
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 2) {
            echo '<p class="success-message">' . "Địa chỉ đã được xóa thành công!" . '</p>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo '<p class="success-message" style="color: red;">Địa chỉ này đã tồn tại!</p>';
        }
        if (isset($error_message)) {
            echo '<p class="success-message" style="color: red;">' . htmlspecialchars($error_message) . '</p>';
        }
        ?>
    </div>

    <!-- Chỉ giữ Leaflet.js, không cần Toastify.js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;
        let mapLoaded = false;

        // Khởi tạo bản đồ khi nhấn nút
        document.getElementById('toggleMapBtn').addEventListener('click', () => {
            if (!mapLoaded) {
                initMap();
                mapLoaded = true;
                document.getElementById('map').style.display = 'block';
            } else {
                document.getElementById('map').style.display = 'block';
            }
        });

        function initMap() {
            map = L.map('map').setView([16.047079, 108.206230], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18
            }).addTo(map);

            map.on('click', (e) => {
                if (marker) marker.remove();
                marker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(map);
                updateCoordinates(e.latlng.lat, e.latlng.lng);

                marker.on('dragend', (e) => {
                    const pos = marker.getLatLng();
                    updateCoordinates(pos.lat, pos.lng);
                });
            });
        }

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            if (marker && map) map.setView([lat, lng], 15);
        }

        // Tải danh sách tỉnh/thành phố
        fetch('https://provinces.open-api.vn/api/p/')
            .then(response => response.json())
            .then(data => {
                const provinceSelect = document.getElementById('province');
                data.forEach(province => {
                    provinceSelect.add(new Option(province.name, province.code));
                });
            });

        document.getElementById('province').addEventListener('change', function () {
            const provinceCode = this.value;
            const districtSelect = document.getElementById('district');
            const communeSelect = document.getElementById('commune');
            districtSelect.innerHTML = '<option value="">Chọn huyện/quận</option>';
            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';

            if (provinceCode) {
                fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`)
                    .then(response => response.json())
                    .then(data => {
                        data.districts.forEach(district => {
                            districtSelect.add(new Option(district.name, district.code));
                        });
                    });
            }
        });

        document.getElementById('district').addEventListener('change', function () {
            const districtCode = this.value;
            const communeSelect = document.getElementById('commune');
            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';

            if (districtCode) {
                fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`)
                    .then(response => response.json())
                    .then(data => {
                        data.wards.forEach(ward => {
                            communeSelect.add(new Option(ward.name, ward.code));
                        });
                    });
            }
        });

        function deleteAddress(address_id) {
            if (confirm('Bạn có chắc chắn muốn xóa địa chỉ này không?')) {
                window.location.href = `index.php?page=addresses&delete_id=${address_id}`;
            }
        }

        function editAddress(address_id, province, district, commune, hamlet, address_detail, recipient_phone, latitude, longitude) {
            document.getElementById('address_id').value = address_id;
            document.getElementById('address_detail').value = address_detail;
            document.getElementById('recipient_phone').value = recipient_phone;
            document.getElementById('hamlet').value = hamlet || '';
            document.getElementById('latitude').value = latitude || '';
            document.getElementById('longitude').value = longitude || '';

            fetch('https://provinces.open-api.vn/api/p/')
                .then(response => response.json())
                .then(data => {
                    const provinceSelect = document.getElementById('province');
                    provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
                    data.forEach(p => {
                        provinceSelect.add(new Option(p.name, p.code, p.code === province));
                    });

                    if (province) {
                        fetch(`https://provinces.open-api.vn/api/p/${province}?depth=2`)
                            .then(response => response.json())
                            .then(data => {
                                const districtSelect = document.getElementById('district');
                                districtSelect.innerHTML = '<option value="">Chọn huyện/quận</option>';
                                data.districts.forEach(d => {
                                    districtSelect.add(new Option(d.name, d.code, d.code === district));
                                });

                                if (district) {
                                    fetch(`https://provinces.open-api.vn/api/d/${district}?depth=2`)
                                        .then(response => response.json())
                                        .then(data => {
                                            const communeSelect = document.getElementById('commune');
                                            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
                                            data.wards.forEach(w => {
                                                communeSelect.add(new Option(w.name, w.code, w.code === commune));
                                            });
                                        });
                                }
                            });
                    }
                });

            document.getElementById('form-title').textContent = 'Sửa Địa Chỉ';
            document.getElementById('submit-btn').textContent = 'Cập Nhật Địa Chỉ';

            if (latitude && longitude) {
                if (!mapLoaded) {
                    initMap();
                    mapLoaded = true;
                    setTimeout(() => setMarkerOnEdit(latitude, longitude), 500); // Chờ bản đồ load
                } else {
                    document.getElementById('map').style.display = 'block';
                    setMarkerOnEdit(latitude, longitude);
                }
            }
        }

        function setMarkerOnEdit(lat, lng) {
            if (marker) marker.remove();
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            map.setView([lat, lng], 15);
            marker.on('dragend', (e) => {
                const pos = marker.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });
        }

        function closeModal() {
            if (window.parent && window.parent.document) {
                const modal = window.parent.document.getElementById('addressModal');
                if (modal) {
                    modal.style.display = 'none'; // Đóng modal thủ công
                }
            }
        }
    </script>
</body>
</html>
<?php
ob_end_flush(); // Gửi buffer và tắt buffering
?>