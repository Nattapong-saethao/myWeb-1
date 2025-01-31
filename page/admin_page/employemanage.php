<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลพนักงาน</title>
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

    // ดึงข้อมูลพนักงานจากตาราง _user
    try {
        $sql = "SELECT user_id, username, surname, email, phone_number, role FROM _user";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }
    ?>
    <div class="main-content">
        <h2>จัดการข้อมูลพนักงานและผู้ดูแลระบบ</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ชื่อ/Name</th>
                    <th>อีเมล/E-mail</th>
                    <th>เบอร์โทรศัพท์/Telephone Number</th>
                    <th>ประเภท/Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($employees): ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['username']) . " " . htmlspecialchars($employee['surname']); ?></td>
                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            <td><?php echo htmlspecialchars($employee['phone_number']); ?></td>
                            <td>
                                <?php if ($employee['role'] == 1) {
                                    echo "Admin";
                                } elseif ($employee['role'] == 2) {
                                    echo "พนักงาน";
                                } else {
                                    echo "ไม่ระบุ";
                                }
                                ?>
                            </td>
                            <td>
                                <form method="POST" action="editemployee.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                                    <button class="btn btn-warning btn-sm">แก้ไข</button>
                                </form>
                                <form method="GET" action="delete_user.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $employee['user_id']; ?>">
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบข้อมูลพนักงานหรือไม่?');">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">ไม่พบข้อมูลพนักงาน</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

</html>
<script>
    function goToDetail() {
        window.location.href = 'editemployee.php';
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