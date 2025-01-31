<?php
session_start(); // เริ่มต้น session
include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็น POST request และมีข้อมูลที่อยู่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['address'])) {
    $newAddress = trim($_POST['address']);
    $member_id = $_SESSION['user_id']; // สมมติว่ามีการเก็บ user_id ใน session

    // ตรวจสอบว่าที่อยู่ไม่ว่าง
    if (empty($newAddress)) {
        echo "กรุณากรอกที่อยู่";  // แจ้งให้กรอกที่อยู่
        exit();
    }

    // คำสั่ง SQL สำหรับการอัปเดตที่อยู่ในฐานข้อมูล
    $sql_update = "UPDATE member SET address = ? WHERE member_id = ?";
    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
        echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        exit();
    }

    $stmt_update->bind_param("si", $newAddress, $member_id);

    if ($stmt_update->execute()) {
        // อัปเดตสำเร็จ
        echo "success";
    } else {
        // ถ้าอัปเดตไม่สำเร็จ
        echo "เกิดข้อผิดพลาดในการอัปเดตที่อยู่";
    }

    $stmt_update->close();
} else {
    echo "ข้อมูลที่ได้รับไม่ถูกต้อง";
}
