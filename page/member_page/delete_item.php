<?php
// เชื่อมต่อฐานข้อมูล
session_start();
include '../php_script/db_connection.php';
// ตรวจสอบว่ามีการส่งค่าผ่าน POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderdetail_id'])) {
    $orderdetail_id = $_POST['orderdetail_id'];

    // ลบข้อมูลจากตาราง orderdetail
    $sql = "DELETE FROM orderdetail WHERE orderdetail_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderdetail_id);

    if ($stmt->execute()) {
        // ลบสำเร็จ, กลับไปที่หน้า cartproduct.php
        header('Location: cartproduct.php');
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
