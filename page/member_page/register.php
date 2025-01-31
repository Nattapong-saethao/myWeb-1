<?php
// Database connection
$servername = "localhost";
$username = "root"; // ใช้ root สำหรับ localhost
$password = ""; // รหัสผ่านสำหรับ root
$dbname = "coffe_shop"; // ชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// รับค่าจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
    $address = $conn->real_escape_string($_POST['address']);

    // ตรวจสอบว่าอีเมลนี้มีอยู่แล้วหรือไม่
    $checkEmail = "SELECT * FROM Member WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "<script>alert('อีเมลนี้มีการลงทะเบียนแล้ว'); window.location.href='register-form.php';</script>";
    } else {
        // เพิ่มข้อมูลลงในตาราง Member
        $sql = "INSERT INTO Member (username, surname, phone_number, email, password, address)
                VALUES ('$username', '$surname', '$phone', '$email', '$password', '$address')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('ลงทะเบียนสำเร็จ!'); window.location.href='login-form.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
