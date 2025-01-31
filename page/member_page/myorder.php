<?php
session_start();

// เชื่อมต่อฐานข้อมูล
include '../php_script/db_connection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// ตรวจสอบว่ามี member_id ใน session หรือไม่
if (!isset($_SESSION['user_id'])) {
    echo "ไม่ได้ล็อกอิน หรือ ไม่มี ID สมาชิกใน session";
    exit;
}
$member_id = $_SESSION['user_id'];
// สร้างคำสั่ง SQL เพื่อดึงข้อมูลคำสั่งซื้อและรายละเอียดสินค้า
$sql = "
     SELECT 
         o.order_id,
         GROUP_CONCAT(p.product_name SEPARATOR '<br>') AS products,
         SUM(od.amount * p.price) AS total_price,
         o.order_date,
         CASE
             WHEN o.status = 'wait' THEN 'กำลังสั่งซื้อ'
             WHEN o.status = 'pending' THEN 'รอดำเนินการ'
             WHEN o.status = 'pickup' THEN 'จัดเตรียมสินค้า'
             WHEN o.status = 'shipped' THEN 'อยู่ระหว่างส่งสินค้า'
         END AS status_label
     FROM orders o
     JOIN orderdetail od ON o.order_id = od.order_id
     JOIN product p ON od.product_id = p.product_id
     WHERE o.status IN ('wait', 'pending', 'pickup', 'shipped')
         AND o.member_id = ? 
     GROUP BY o.order_id
     ORDER BY o.order_date DESC
 ";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $member_id);
if (!$stmt->execute()) {
    echo "SQL execute failed: " . $stmt->error;
    exit;
}
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <div class="container mt-5">
        <h2 style="text-align: center;line-height: 65px;">คำสั่งซื้อของฉัน</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ลำดับที่</th>
                    <th>สินค้า</th>
                    <th>ราคารวม</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>สถานะ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ตรวจสอบว่ามีข้อมูลในผลลัพธ์หรือไม่
                if (!empty($rows)) {
                    $counter = 1;
                    foreach ($rows as $row) {
                        $status_label = $row['status_label'];
                        $details_url = '';
                        switch ($status_label) {
                            case 'รอดำเนินการ':
                                $details_url = 'ordertracking1.php?order_id=' . $row['order_id']; // สถานะรอดำเนินการ
                                break;
                            case 'จัดเตรียมสินค้า':
                                $details_url = 'ordertracking_pickup.php?order_id=' . $row['order_id']; // สถานะจัดเตรียมสินค้า
                                break;
                            case 'อยู่ระหว่างส่งสินค้า':
                                $details_url = 'ordertracking.php?order_id=' . $row['order_id']; // สถานะอยู่ระหว่างส่งสินค้า
                                break;
                            case 'เสร็จสิ้น':
                                $details_url = 'ordertracking.php?order_id=' . $row['order_id']; // สถานะเสร็จสิ้น
                                break;
                            default:
                                $details_url = 'ordertracking.php?order_id=' . $row['order_id'];
                        }

                        // แสดงข้อมูลในตาราง
                        echo "<tr>";
                        echo "<td>" . $counter . "</td>";
                        echo "<td>" . $row['products'] . "</td>";
                        echo "<td>" . number_format($row['total_price'], 2) . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['order_date'])) . "</td>";
                        echo "<td>" . $status_label . "</td>";
                        echo "<td align='center'>";

                        // ปุ่มรายละเอียด
                        echo "<form method='POST' action='" . $details_url . "' style='display:inline;'>";
                        echo "<input type='hidden' name='order_id' value='" . $row['order_id'] . "'>";
                        echo "<button type='submit' class='btn btn-primary'>รายละเอียด</button>";
                        echo "</form>";

                        // เงื่อนไขสำหรับแสดงปุ่มยกเลิกคำสั่งซื้อ
                        if ($status_label != 'ยกเลิกแล้ว' && $status_label != 'อยู่ระหว่างส่งสินค้า' && $status_label != 'เสร็จสิ้น') {
                            echo "<form method='POST' action='cancel_order.php' style='display:inline;'>";
                            echo "<input type='hidden' name='order_id' value='" . $row['order_id'] . "'>";
                            echo "<button type='submit' class='btn btn-danger'>ยกเลิกคำสั่งซื้อ</button>";
                            echo "</form>";
                        }

                        echo "</td>";
                        echo "</tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='6'>ไม่มีข้อมูลคำสั่งซื้อ</td></tr>";
                }

                ?>
            </tbody>
        </table>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<?php
// ปิดการเชื่อมต่อ
$conn->close();
?>
<style>
    body {
        background-image: url("/myWeb/image/background.jpg");
        background-size: cover;
        height: 100vh;
        background-repeat: no-repeat;
        background-position: center;
        font-family: 'Arial', sans-serif;
    }

    .navbar {
        background-color: #A67B5B;
    }

    .navbar-brand {
        font-weight: bold;
        color: #fff;
    }

    .navbar-nav .nav-link {
        color: rgb(0, 0, 0);
    }

    .navbar-nav .nav-link:hover {
        background-color: #A67B5B;
        color: #fff;
        border-radius: 5px;
    }

    .container.mt-5 {
        margin-top: 50px;
        background-color: #fff;
        height: 70%;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>