<?php
session_start();
include '../php_script/db_connection.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $sql = "DELETE FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'ลบสินค้าสำเร็จ';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'เกิดข้อผิดพลาดในการลบสินค้า3: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = "Invalid request.";
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
