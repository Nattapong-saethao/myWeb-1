<?php
session_start();

// ลบ session ทั้งหมด
session_unset();

// ทำลาย session
session_destroy();

// เปลี่ยนเส้นทางไปยังหน้า login
header("Location: ../member_page/login-form.php"); // เปลี่ยน "login.php" เป็นหน้า login ของคุณ
exit;
