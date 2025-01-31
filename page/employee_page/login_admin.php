<?php
// เริ่มต้น session
session_start();
include '../php_script/db_connection.php';

// รับค่าจากฟอร์ม
$email = $_POST['email'];
$password = $_POST['password'];

// ตรวจสอบข้อมูลผู้ใช้
$sql = "SELECT user_id, role, password, username FROM _user WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $role, $hashed_password, $username);
    $stmt->fetch();

    // ตรวจสอบรหัสผ่าน
    if (password_verify($password, $hashed_password)) {
        // เก็บข้อมูลผู้ใช้ใน session
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;

        // ตรวจสอบบทบาทและเปลี่ยนเส้นทาง
        if ($role == 1) {
            header("Location:../admin_page/dashboard_admin.php");
        } elseif ($role == 2) {
            header("Location: ../employee_page/order.php");
        } else {
            echo "Unauthorized role.";
        }
    } else {
        // รหัสผ่านไม่ถูกต้อง
        header("Location: /admin_login_form.php?error=invalid_password");
    }
} else {
    // อีเมลไม่ถูกต้อง
    header("Location: /admin_login_form.php?error=invalid_email");
}

$stmt->close();
$conn->close();
