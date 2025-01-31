<?php
session_start();
include '../php_script/db_connection.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $username = $_POST['username'];
    $surname = $_POST['surname'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];

    $stmt = $conn->prepare("INSERT INTO _user (role, username, surname, password, email, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $role, $username, $surname, $password, $email, $phone);

    if ($stmt->execute()) {
        $response['message'] = 'เพิ่มผู้ใช้งานสำเร็จ';
    } else {
        $response['message'] = 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้งาน';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
