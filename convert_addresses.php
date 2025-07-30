<?php
$conn = new mysqli("localhost", "root", "", "phonedb");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Hàm gọi API để lấy tên từ mã
function getNameFromCode($type, $code) {
    $url = "https://provinces.open-api.vn/api/";
    switch ($type) {
        case 'province':
            $url .= "p/$code";
            break;
        case 'district':
            $url .= "d/$code";
            break;
        case 'commune':
            $url .= "w/$code";
            break;
    }
    $data = @file_get_contents($url);
    if ($data !== false) {
        $json = json_decode($data, true);
        return $json['name'] ?? $code;
    }
    return $code; // Trả về mã nếu API lỗi
}

// Lấy tất cả địa chỉ
$sql = "SELECT address_id, province, district, commune FROM tbl_addresses";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $address_id = $row['address_id'];
        $province_code = $row['province'];
        $district_code = $row['district'];
        $commune_code = $row['commune'];

        // Lấy tên từ mã
        $province_name = getNameFromCode('province', $province_code);
        $district_name = getNameFromCode('district', $district_code);
        $commune_name = getNameFromCode('commune', $commune_code);

        // Cập nhật bảng tbl_addresses
        $update_sql = "UPDATE tbl_addresses SET province = ?, district = ?, commune = ? WHERE address_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $province_name, $district_name, $commune_name, $address_id);
        if ($update_stmt->execute()) {
            echo "Cập nhật địa chỉ ID $address_id thành công: $province_name, $district_name, $commune_name<br>";
        } else {
            echo "Lỗi khi cập nhật địa chỉ ID $address_id: " . $conn->error . "<br>";
        }
        $update_stmt->close();
    }
} else {
    echo "Không có địa chỉ nào để cập nhật.";
}

$conn->close();
?>