<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<?php
session_start();
include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // ดึงข้อมูลจากฐานข้อมูล
    $query = "SELECT * FROM Member WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ตรวจสอบว่ามีผู้ใช้ในฐานข้อมูลหรือไม่
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        // ถ้าไม่พบข้อมูลผู้ใช้ในฐานข้อมูล
        echo "<script>alert('ไม่พบข้อมูลผู้ใช้'); window.location.href='editprofile.php';</script>";
        exit;
    }

    $stmt->close();
} else {
    // ถ้าผู้ใช้ไม่ได้ล็อกอิน
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อน'); window.location.href='login.php';</script>";
    exit;
}
?>

<body>
    <div class="container mt-5">
        <div class="register-container p-4 border rounded">
            <h3 class="text-center mb-4">แก้ไขข้อมูลส่วนตัว</h3>
            <form id="registerForm" method="POST" action="editprofile.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Name/ชื่อ</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $row['username']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="surname" class="form-label">Surname/นามสกุล</label>
                        <input type="text" class="form-control" id="surname" name="surname" value="<?php echo $row['surname']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number/หมายเลขโทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $row['phone_number']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email/อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password/รหัสผ่าน</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter New Password">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="address" class="form-label">Address/ที่อยู่</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo $row['address']; ?></textarea>
                    </div>
                </div>
                <div class="text-center">
                    <button type="reset" class="btn btn-primary me-2" onclick="window.location.href='home.php' ">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary ">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</body>


</html>
<style>
    body {
        background-image: url("/myWeb/image/background.jpg");
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        font-family: 'Arial', sans-serif;
    }

    .register-container {
        max-width: 700px;
        margin: 100px auto;
        padding: 20px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .register-btn {
        background-color: #5b3c1e;
        color: #fff;
        border: none;
    }

    .register-btn:hover {
        background-color: #4a2e16;
    }
</style>