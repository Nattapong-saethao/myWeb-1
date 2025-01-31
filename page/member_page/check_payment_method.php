<?php
include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $sql = "SELECT payment_method FROM payment WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'payment_method' => $row['payment_method'] // ส่งข้อมูลวิธีการชำระเงิน
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
