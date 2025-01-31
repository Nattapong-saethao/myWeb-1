<?php
session_start();

include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $query = "SELECT * FROM member WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $row['password'])) {
            // ตั้งค่า session เมื่อล็อกอินสำเร็จ
            $_SESSION['user_id'] = $row['member_id'];
            $_SESSION['user_name'] = $row['username'];
            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); window.location.href='login-form.php';</script>";
        }
    } else {
        echo "<script>alert('อีเมลไม่ถูกต้อง'); window.location.href='login-form.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
