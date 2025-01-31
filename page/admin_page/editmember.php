<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลสมาชิก</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content {
            margin-left: 250px;
            /* Adjust based on your sidebar width */
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    include '../php_script/db_connection.php';

    // ตรวจสอบว่ามี member_id ใน $_POST หรือไม่
    if (isset($_POST['member_id'])) {
        $member_id = $_POST['member_id'];

        // ดึงข้อมูลสมาชิกจากฐานข้อมูล
        try {
            $sql = "SELECT username, surname, address, phone_number, email FROM member WHERE member_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $member = $result->fetch_assoc();
            } else {
                echo "<p>ไม่พบข้อมูลสมาชิก</p>";
                exit;
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            echo "Database Error: " . $e->getMessage();
            die();
        }
    } else {
        echo "<p>ไม่พบ member_id</p>";
        exit;
    }

    // เมื่อมีการกดปุ่มบันทึก
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $new_username = $_POST['username'];
        $new_surname = $_POST['surname'];
        $new_address = $_POST['address'];
        $new_phone_number = $_POST['phone_number'];
        $new_email = $_POST['email'];

        // ตรวจสอบว่ามีการป้อนรหัสผ่านใหม่หรือไม่
        if (!empty($_POST['password'])) {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่านใหม่
            $sql_update = "UPDATE member SET username = ?, surname = ?, address = ?, phone_number = ?, email = ?, password = ? WHERE member_id = ?";
        } else {
            $sql_update = "UPDATE member SET username = ?, surname = ?, address = ?, phone_number = ?, email = ? WHERE member_id = ?";
        }

        try {
            $stmt_update = $conn->prepare($sql_update);

            if (!empty($_POST['password'])) {
                $stmt_update->bind_param("ssssssi", $new_username, $new_surname, $new_address, $new_phone_number, $new_email, $new_password, $member_id);
            } else {
                $stmt_update->bind_param("sssssi", $new_username, $new_surname, $new_address, $new_phone_number, $new_email, $member_id);
            }

            if ($stmt_update->execute()) {
                echo "<p>แก้ไขข้อมูลสมาชิกสำเร็จ</p>";
                header("Location: membermanage.php");
                exit;
            } else {
                echo "<p>เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $stmt_update->error . "</p>";
            }
            $stmt_update->close();
        } catch (mysqli_sql_exception $e) {
            echo "Database Error: " . $e->getMessage();
            die();
        }
    }
    ?>
    <div class="content">
        <h2>แก้ไขข้อมูลสมาชิก</h2>
        <form method="POST">
            <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
            <div class="form-group">
                <label for="username">ชื่อ</label>
                <input type="text" class="form-control" id="username" name="username" style="max-width: 350px;" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="surname">นามสกุล</label>
                <input type="text" class="form-control" id="surname" name="surname" style="max-width: 350px;" value="<?php echo htmlspecialchars($member['surname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">ที่อยู่</label>
                <textarea class="form-control" id="address" name="address" style="max-width: 350px;" rows="3" required><?php echo htmlspecialchars($member['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="phone_number">เบอร์โทรศัพท์</label>
                <input type="tel" class="form-control" id="phone_number" name="phone_number" style="max-width: 350px;" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" placeholder="เช่น 0812345678" value="<?php echo htmlspecialchars($member['phone_number']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">อีเมลล์</label>
                <input type="email" class="form-control" id="email" name="email" style="max-width: 350px;" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                <input type="password" class="form-control" id="password" name="password" style="max-width: 350px;" placeholder="ใส่รหัสผ่านใหม่">
            </div>
            <button type="submit" class="btn btn-success" name="update">บันทึก</button>
        </form>
    </div>
</body>

</html>

<style>
    body {
        display: flex;
    }

    .sidebar {
        position: fixed;
        /* ทำให้ sidebar คงที่ */
        top: 0;
        left: 0;
        height: 100vh;
        /* ความสูงเท่ากับ 100% ของ viewport */
        width: 200px;
        /* กำหนดความกว้างของ sidebar */
        background-color: #343a40;
        /* สีพื้นหลัง */
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar a {
        display: block;
        padding: 10px;
        color: #333;
        text-decoration: none;
    }

    .sidebar a:hover {
        background-color: #ddd;
    }

    .content {
        margin-left: 200px;
        /* เว้นระยะทางซ้ายให้เท่ากับ sidebar */
        padding: 20px;
        width: calc(100% - 200px);
        /* คำนวณความกว้างใหม่ให้พอดีกับหน้าจอ */
        box-sizing: border-box;
        /* รวม padding ไว้ในการคำนวณ width */
    }
</style>