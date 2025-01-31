<?php
$servername = "localhost";
$username = "root"; // ค่าเริ่มต้นของ XAMPP คือ "root"
$password = ""; // ค่าเริ่มต้นของ XAMPP คือค่าว่าง
$dbname = "coffe_shop"; // ชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
