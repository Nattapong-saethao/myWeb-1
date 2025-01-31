<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหน่วยนับ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>จัดการหน่วยนับ</h2>
        <?php
        session_start();
        include 'sidebar.php';
        include '../php_script/db_connection.php';

        // ตรวจสอบว่ามีการส่ง action มาทาง POST หรือไม่
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                if ($action == "add") {
                    // Add Unit
                    $unit_name = $_POST['unit_name'];

                    if (empty($unit_name)) {
                        echo '<div class="alert alert-danger">กรุณากรอกชื่อหน่วยนับ</div>';
                    } else {
                        $sql = "INSERT INTO units (unit_name) VALUES (?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $unit_name);
                        if ($stmt->execute()) {
                            echo '<div class="alert alert-success">เพิ่มหน่วยนับสำเร็จ</div>';
                        } else {
                            echo '<div class="alert alert-danger">เพิ่มหน่วยนับไม่สำเร็จ: ' . $stmt->error . '</div>';
                        }
                    }
                } else if ($action == "edit") {
                    // Edit Unit
                    $unit_id = $_POST['unit_id'];
                    $unit_name = $_POST['unit_name'];
                    if (empty($unit_name)) {
                        echo '<div class="alert alert-danger">กรุณากรอกชื่อหน่วยนับ</div>';
                    } else {
                        $sql = "UPDATE units SET unit_name = ? WHERE unit_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $unit_name, $unit_id);
                        if ($stmt->execute()) {
                            echo '<div class="alert alert-success">แก้ไขหน่วยนับสำเร็จ</div>';
                        } else {
                            echo '<div class="alert alert-danger">แก้ไขหน่วยนับไม่สำเร็จ: ' . $stmt->error . '</div>';
                        }
                    }
                } else if ($action == "delete") {
                    // Delete Unit
                    $unit_id = $_POST['unit_id'];

                    $sql = "DELETE FROM units WHERE unit_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $unit_id);

                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success">ลบหน่วยนับสำเร็จ</div>';
                    } else {
                        echo '<div class="alert alert-danger">ลบหน่วยนับไม่สำเร็จ: ' . $stmt->error . '</div>';
                    }
                }
            }
        }

        // ดึงข้อมูลหน่วยนับทั้งหมดจากฐานข้อมูล
        $sql = "SELECT unit_id, unit_name FROM units";
        $result = $conn->query($sql);
        ?>

        <!-- Form เพิ่มหน่วยนับ -->
        <h3>เพิ่มหน่วยนับ</h3>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="unit_name">ชื่อหน่วยนับ:</label>
                <input type="text" class="form-control" name="unit_name" required>
            </div>
            <input type="hidden" name="action" value="add">
            <button type="submit" class="btn btn-primary">เพิ่มหน่วยนับ</button>
        </form>
        <hr>
        <!-- ตารางแสดงหน่วยนับและปุ่มแก้ไข -->
        <h3>แก้ไข/ลบหน่วยนับ</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อหน่วยนับ</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['unit_id'] . "</td>";
                        echo "<td>" . $row['unit_name'] . "</td>";
                        echo '<td>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal' . $row['unit_id'] . '">แก้ไข</button>
                            </td>';
                        echo '<td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal' . $row['unit_id'] . '">ลบ</button>
                        </td>';
                        echo "</tr>";

                        // Modal แก้ไข
                        echo '
                <div class="modal fade" id="editModal' . $row['unit_id'] . '" tabindex="-1" role="dialog" aria-labelledby="editModalLabel' . $row['unit_id'] . '" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel' . $row['unit_id'] . '">แก้ไขหน่วยนับ</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="post" action="' . $_SERVER['PHP_SELF'] . '">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="unit_id" value="' . $row['unit_id'] . '">
                                    <div class="form-group">
                                        <label for="unit_name">ชื่อหน่วยนับ:</label>
                                        <input type="text" class="form-control" name="unit_name" value="' . $row['unit_name'] . '" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            ';

                        // Modal ลบ
                        echo '
            <div class="modal fade" id="deleteModal' . $row['unit_id'] . '" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel' . $row['unit_id'] . '" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel' . $row['unit_id'] . '">ยืนยันการลบ</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>คุณต้องการลบหน่วยนับ: <strong>' . $row['unit_name'] . '</strong> หรือไม่?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                            <form method="post" action="' . $_SERVER['PHP_SELF'] . '">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="unit_id" value="' . $row['unit_id'] . '">
                                <button type="submit" class="btn btn-danger">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        ';
                    }
                } else {
                    echo "<tr><td colspan='4'>ไม่มีข้อมูลหน่วยนับ</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

    .table th,
    .table td {
        vertical-align: middle;
    }