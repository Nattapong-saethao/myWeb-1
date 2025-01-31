<?php
session_start();
include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

//update_profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $username = $_POST['username'];
        $surname = $_POST['surname'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $address = $_POST['address'];

        // ดึงข้อมูลรหัสผ่านเก่าจากฐานข้อมูล
        $query = "SELECT password FROM Member WHERE member_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // ตรวจสอบว่ามีการกรอกรหัสผ่านใหม่หรือไม่
        if (!empty($password)) {
            // ถ้ามีการกรอกรหัสผ่านใหม่ให้ทำการเข้ารหัสรหัสผ่านใหม่
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            // ถ้าไม่มีการกรอกพาสเวิร์ดใหม่, ใช้พาสเวิร์ดเดิมจากฐานข้อมูล
            $hashed_password = $row['password'];
        }

        // อัปเดตข้อมูลในฐานข้อมูล
        $query = "UPDATE Member SET username = ?, surname = ?, phone_number = ?, email = ?, password = ?, address = ? WHERE member_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $username, $surname, $phone, $email, $hashed_password, $address, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('ข้อมูลถูกอัปเดตเรียบร้อยแล้ว'); window.location.href='home.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล'); window.location.href='editprofile.php';</script>";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "กรุณาเข้าสู่ระบบก่อน";
    }
}
