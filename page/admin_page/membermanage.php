<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลสมาชิก</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
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

    // ดึงข้อมูลสมาชิกจากตาราง member
    try {
        $sql = "SELECT member_id, username, surname, email, address, phone_number FROM member";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $members = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }
    ?>
    <div class="main-content">
        <h2>จัดการข้อมูลสมาชิก</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ชื่อ/Name</th>
                    <th>อีเมล/E-mail</th>
                    <th>เบอร์โทรศัพท์/Telephone Number</th>
                    <th>ที่อยู่/Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members) : ?>
                    <?php foreach ($members as $member) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['username']) . " " . htmlspecialchars($member['surname']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($member['address']); ?></td>
                            <td>
                                <form method="POST" action="editmember.php" style="display:inline;">
                                    <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                    <button class="btn btn-warning btn-sm">แก้ไข</button>
                                </form>

                                <button class="btn btn-danger btn-sm" onclick='deletemember("<?php echo $member["member_id"]; ?>")'>ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">ไม่พบข้อมูลสมาชิก</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<script>
    function goToDetail() {
        window.location.href = 'editmember.php';
    }

    function deletemember(memberId) {
        // แทนที่ด้วยโค้ดที่คุณต้องการให้ทำเมื่อคลิกปุ่ม "ลบ"
        console.log("ลบคำสั่งซื้อ: " + memberId);
        if (confirm("คุณต้องการลบสมาชิกหมายเลข: " + memberId + " จริงหรือไม่?")) {
            // หากผู้ใช้ยืนยันให้ลบ ทำการส่งคำขอไปลบที่ Server

            //ตัวอย่างการ redirect
            window.location.href = 'delete_member.php?id=' + memberId;
            alert("ทำการลบสมาชิกหมายเลข: " + memberId);
        }


    }
</script>
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

    .main-content {
        margin-left: 200px;
        /* เว้นระยะทางซ้ายให้เท่ากับ sidebar */
        padding: 20px;
        width: calc(100% - 200px);
        /* คำนวณความกว้างใหม่ให้พอดีกับหน้าจอ */
        box-sizing: border-box;
        /* รวม padding ไว้ในการคำนวณ width */
    }

    .table th,
    .table td {
        text-align: center;
    }
</style>