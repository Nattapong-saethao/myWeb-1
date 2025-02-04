<?php
$servername = "dpg-cuem0q56l47c73eu2ja0-a";
$username = "coffe_shop_jv6p_user"; // ค่าเริ่มต้นของ XAMPP คือ "root"
$password = "WXWfnJt0cmZZv1o5rJPCGchwNiiwPZLK"; // ค่าเริ่มต้นของ XAMPP คือค่าว่าง
$dbname = "coffe_shop_jv6p"; // ชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
