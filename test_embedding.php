<?php
$pythonScriptPath = 'C:\\xampp2\\htdocs\\phone-shop\\embeddings.py';
$conn = new mysqli('127.0.0.1', 'root', '', 'phonedb');
$conn->set_charset('utf8mb4');

function callEmbeddingAPI($query, $products, $conn, $pythonScriptPath) {
    $query = $conn->real_escape_string($query);
    $inputData = json_encode(['query' => $query, 'products' => $products]);
    $command = escapeshellcmd('"C:\\Users\\kanek\\AppData\\Local\\Programs\\Python\\Python313\\python.exe" "' . $pythonScriptPath . '"');
    $descriptorspec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];
    $process = proc_open($command, $descriptorspec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $inputData);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $return_value = proc_close($process);
        if ($return_value !== 0) {
            error_log("Lỗi khi gọi script Python: $error", 3, 'C:\\xampp2\\logs\\php_errors.log');
            return array_slice($products, 0, 3);
        }
        $topProducts = json_decode($output, true);
        if ($topProducts === null) {
            error_log("Lỗi phân tích JSON từ script Python: " . json_last_error_msg(), 3, 'C:\\xampp2\\logs\\php_errors.log');
            return array_slice($products, 0, 3);
        }
        return $topProducts;
    }
    error_log("Không thể chạy script Python", 3, 'C:\\xampp2\\logs\\php_errors.log');
    return array_slice($products, 0, 3);
}

$query = "điện thoại chơi game";
$products = [
    [
        'product_id' => 1,
        'product_name' => 'iPhone 13',
        'price' => 20000000,
        'rear_camera' => '12MP',
        'chipset' => 'A15 Bionic',
        'battery' => '3240mAh',
        'image_url' => 'iphone13.jpg'
    ],
    [
        'product_id' => 2,
        'product_name' => 'Samsung Galaxy S21',
        'price' => 18000000,
        'rear_camera' => '64MP',
        'chipset' => 'Exynos 2100',
        'battery' => '4000mAh',
        'image_url' => 'samsungs21.jpg'
    ],
    [
        'product_id' => 3,
        'product_name' => 'Xiaomi Poco X3',
        'price' => 6000000,
        'rear_camera' => '48MP',
        'chipset' => 'Snapdragon 732G',
        'battery' => '5160mAh',
        'image_url' => 'poco_x3.jpg'
    ]
];
$result = callEmbeddingAPI($query, $products, $conn, $pythonScriptPath);
echo json_encode($result, JSON_PRETTY_PRINT);
?>