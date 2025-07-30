<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? 5; // Giá trị mặc định là 5 nếu session không có

// Khởi tạo kết nối cơ sở dữ liệu
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
            header("Location: index.php?page=addresses&success=2"); // Chuyển hướng sau khi xóa
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
    $province_name = $_POST['province_name'] ?? '';
    $district_name = $_POST['district_name'] ?? '';
    $commune_name = $_POST['commune_name'] ?? '';
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
            $update_stmt->bind_param("sssssssddi", $recipient_phone, $province_name, $district_name, $commune_name, $hamlet, $address_detail, $latitude, $longitude, $address_id, $user_id);
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
            $check_stmt->bind_param("isssss", $user_id, $province_name, $district_name, $commune_name, $hamlet, $address_detail);
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
                    $insert_stmt->bind_param("issssssdd", $user_id, $recipient_phone, $province_name, $district_name, $commune_name, $hamlet, $address_detail, $latitude, $longitude);
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
                        echo 'window.history.back();'; // Quay lại trang trước ngay lập tức
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Quản Lý Địa Chỉ</h2>

        <!-- Danh sách địa chỉ -->
        <div class="address-list mb-4">
            <?php
            $sql = "SELECT address_id, province, district, commune, hamlet, address_detail, recipient_phone, latitude, longitude FROM tbl_addresses WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $full_address = htmlspecialchars($row['address_detail']) . ', ' . 
                                    htmlspecialchars($row['hamlet']) . ', ' . 
                                    htmlspecialchars($row['commune']) . ', ' . 
                                    htmlspecialchars($row['district']) . ', ' . 
                                    htmlspecialchars($row['province']);

                    echo '<div class="card mb-3">';
                    echo '<div class="card-body">';
                    echo '<p class="card-text"><strong>Địa chỉ:</strong> ' . $full_address . '</p>';
                    echo '<p class="card-text"><strong>Số điện thoại:</strong> ' . htmlspecialchars($row['recipient_phone']) . '</p>';
                    if ($row['latitude'] && $row['longitude']) {
                        echo '<p class="card-text"><strong>Tọa độ:</strong> Vĩ độ: ' . htmlspecialchars($row['latitude']) . ', Kinh độ: ' . htmlspecialchars($row['longitude']) . '</p>';
                    }
                    echo '<div class="d-flex gap-2">';
                    echo '<button class="btn btn-warning edit-btn" onclick="editAddress(' . $row['address_id'] . ', \'' . htmlspecialchars($row['province']) . '\', \'' . htmlspecialchars($row['district']) . '\', \'' . htmlspecialchars($row['commune']) . '\', \'' . htmlspecialchars($row['hamlet']) . '\', \'' . htmlspecialchars($row['address_detail']) . '\', \'' . htmlspecialchars($row['recipient_phone']) . '\', ' . ($row['latitude'] ?? 'null') . ', ' . ($row['longitude'] ?? 'null') . ')">Sửa</button>';
                    echo '<button class="btn btn-danger delete-btn" onclick="deleteAddress(' . $row['address_id'] . ')">Xóa</button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-muted">Chưa có địa chỉ nào được lưu.</p>';
            }
            $stmt->close();
            ?>
        </div>

        <!-- Form thêm/cập nhật địa chỉ -->
        <div class="card p-4">
            <h2 id="form-title" class="mb-4">Thêm Địa Chỉ Mới</h2>
            <form id="addressForm" method="POST" action="">
                <input type="hidden" id="address_id" name="address_id">
                <input type="hidden" id="isModal" name="isModal" value="<?php echo isset($_GET['isModal']) && $_GET['isModal'] == 1 ? 1 : 0; ?>">
                <input type="hidden" id="province_name" name="province_name">
                <input type="hidden" id="district_name" name="district_name">
                <input type="hidden" id="commune_name" name="commune_name">
                <div class="mb-3">
                    <label for="province" class="form-label">Tỉnh/Thành phố:</label>
                    <select id="province" name="province" class="form-select" required>
                        <option value="">Chọn tỉnh/thành phố</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="district" class="form-label">Huyện/Quận:</label>
                    <select id="district" name="district" class="form-select" required>
                        <option value="">Chọn huyện/quận</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="commune" class="form-label">Xã/Phường:</label>
                    <select id="commune" name="commune" class="form-select" required>
                        <option value="">Chọn xã/phường</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="hamlet" class="form-label">Ấp/Xóm/Thôn:</label>
                    <input type="text" id="hamlet" name="hamlet" class="form-control" placeholder="Nhập tên ấp/thôn/xóm">
                </div>
                <div class="mb-3">
                    <label for="address_detail" class="form-label">Địa chỉ chi tiết:</label>
                    <input type="text" id="address_detail" name="address_detail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="recipient_phone" class="form-label">Số điện thoại:</label>
                    <input type="text" id="recipient_phone" name="recipient_phone" class="form-control" pattern="[0-9]{10}" required>
                </div>
                <div class="mb-3">
                    <label for="latitude" class="form-label">Vĩ độ (Latitude):</label>
                    <input type="number" step="any" id="latitude" name="latitude" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="longitude" class="form-label">Kinh độ (Longitude):</label>
                    <input type="number" step="any" id="longitude" name="longitude" class="form-control" readonly>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button type="submit" id="submit-btn" class="btn btn-primary">Lưu Địa Chỉ</button>
                    <button type="button" class="btn btn-secondary exit-btn" onclick="closeModal()">Thoát</button>
                </div>
            </form>
            <button id="toggleMapBtn" class="btn btn-success mb-3">Thêm vị trí trên bản đồ</button>
            <div id="map"></div>
        </div>

        <!-- Thông báo lỗi nếu có -->
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 2) {
            echo '<p class="success-message">Địa chỉ đã được xóa thành công!</p>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo '<p class="error-message">Địa chỉ này đã tồn tại!</p>';
        }
        if (isset($error_message)) {
            echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
        }
        ?>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            })
            .catch(error => {
                console.error('Lỗi khi tải tỉnh:', error);
                document.getElementById('province').innerHTML = '<option value="">Không thể tải tỉnh</option>';
            });

        document.getElementById('province').addEventListener('change', function () {
            const provinceCode = this.value;
            const provinceName = this.options[this.selectedIndex].text;
            const districtSelect = document.getElementById('district');
            const communeSelect = document.getElementById('commune');
            districtSelect.innerHTML = '<option value="">Chọn huyện/quận</option>';
            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            document.getElementById('province_name').value = provinceName;

            if (provinceCode) {
                fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`)
                    .then(response => response.json())
                    .then(data => {
                        data.districts.forEach(district => {
                            districtSelect.add(new Option(district.name, district.code));
                        });
                    })
                    .catch(error => {
                        console.error('Lỗi khi tải huyện:', error);
                        districtSelect.innerHTML = '<option value="">Không thể tải huyện</option>';
                    });
            }
        });

        document.getElementById('district').addEventListener('change', function () {
            const districtCode = this.value;
            const districtName = this.options[this.selectedIndex].text;
            const communeSelect = document.getElementById('commune');
            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            document.getElementById('district_name').value = districtName;

            if (districtCode) {
                fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`)
                    .then(response => response.json())
                    .then(data => {
                        data.wards.forEach(ward => {
                            communeSelect.add(new Option(ward.name, ward.code));
                        });
                    })
                    .catch(error => {
                        console.error('Lỗi khi tải xã:', error);
                        communeSelect.innerHTML = '<option value="">Không thể tải xã</option>';
                    });
            }
        });

        document.getElementById('commune').addEventListener('change', function () {
            const communeName = this.options[this.selectedIndex].text;
            document.getElementById('commune_name').value = communeName;
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
            document.getElementById('province_name').value = province || '';
            document.getElementById('district_name').value = district || '';
            document.getElementById('commune_name').value = commune || '';

            fetch('https://provinces.open-api.vn/api/p/')
                .then(response => response.json())
                .then(data => {
                    const provinceSelect = document.getElementById('province');
                    provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
                    data.forEach(p => {
                        const selected = p.name === province;
                        provinceSelect.add(new Option(p.name, p.code, selected));
                        if (selected) document.getElementById('province_name').value = p.name;
                    });

                    if (province) {
                        fetch(`https://provinces.open-api.vn/api/p/${data.find(p => p.name === province)?.code}?depth=2`)
                            .then(response => response.json())
                            .then(data => {
                                const districtSelect = document.getElementById('district');
                                districtSelect.innerHTML = '<option value="">Chọn huyện/quận</option>';
                                data.districts.forEach(d => {
                                    const selected = d.name === district;
                                    districtSelect.add(new Option(d.name, d.code, selected));
                                    if (selected) document.getElementById('district_name').value = d.name;
                                });

                                if (district) {
                                    fetch(`https://provinces.open-api.vn/api/d/${data.districts.find(d => d.name === district)?.code}?depth=2`)
                                        .then(response => response.json())
                                        .then(data => {
                                            const communeSelect = document.getElementById('commune');
                                            communeSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
                                            data.wards.forEach(w => {
                                                const selected = w.name === commune;
                                                communeSelect.add(new Option(w.name, w.code, selected));
                                                if (selected) document.getElementById('commune_name').value = w.name;
                                            });
                                        })
                                        .catch(error => console.error('Lỗi khi tải xã:', error));
                                }
                            })
                            .catch(error => console.error('Lỗi khi tải huyện:', error));
                    }
                })
                .catch(error => console.error('Lỗi khi tải tỉnh:', error));

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
ob_end_flush(); 
?>