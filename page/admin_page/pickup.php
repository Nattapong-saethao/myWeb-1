<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    include '../php_script/db_connection.php';
    ?>
    <div class="content">
        <h2>จัดการคำสั่งซื้อที่อยู่ระหว่างจัดส่ง</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>คำสั่งซื้อ/Order</th>
                    <th>รายการสินค้า/Product list</th>
                    <th>ชื่อ/Name</th>
                    <th>ที่อยู่/Address</th>
                    <th>วันที่สั่งซื้อ/Order date</th>
                    <th>สถานะ/Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // SQL query สำหรับดึงข้อมูล orders ที่มีสถานะ "shipped" พร้อมข้อมูลสมาชิกและสินค้า
                $sql = "SELECT
                            o.order_id,
                            GROUP_CONCAT(CONCAT(p.product_name, ' (', od.amount, ' ', u.unit_name, ') ') SEPARATOR ', ') AS product_list,
                            m.username,
                            m.surname,
                            m.address,
                            o.order_date,
                            o.status
                        FROM
                            orders o
                        JOIN
                            member m ON o.member_id = m.member_id
                        JOIN
                            orderdetail od ON o.order_id = od.order_id
                        JOIN
                            product p ON od.product_id = p.product_id
                        JOIN
                            units u ON p.unit_id = u.unit_id
                        WHERE
                            o.status = 'shipped' AND od.selected = 1
                        GROUP BY
                            o.order_id, m.username, m.surname, m.address, o.order_date, o.status";


                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        echo "<tr>";
                        echo "<td>" . $row["order_id"] . "</td>";
                        echo "<td>" . $row["product_list"] . "</td>";
                        echo "<td>" . $row["username"] . " " . $row["surname"] . "</td>";
                        echo "<td>" . $row["address"] . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row["order_date"])) . "</td>"; // แปลงรูปแบบวันที่
                        echo "<td>อยู่ระหว่างจัดส่ง</td>";
                        echo "<td>
                            <a href='#' class='btn btn-primary ' onclick='goToDetail2(\"" . $row["order_id"] . "\")'>รายละเอียด</a>
                            <a href='#' class='btn btn-danger btn-sm' onclick='deleteOrder(\"" . $row["order_id"] . "\")'>ลบ</a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>ไม่พบคำสั่งซื้อที่อยู่ในสถานะ อยู่ระหว่างจัดส่ง หรือไม่มีสินค้าที่ถูกเลือก</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function goToDetail2(orderId) {
            // แทนที่ด้วยโค้ดที่คุณต้องการให้ทำเมื่อคลิกปุ่ม "แก้ไข"
            console.log("แก้ไขคำสั่งซื้อ: " + orderId);
            alert("คุณต้องการแก้ไขคำสั่งซื้อหมายเลข: " + orderId);

            //ตัวอย่างการ redirect
            window.location.href = 'checkorder.php?id=' + orderId + '&status=' + 'shipped';
        }

        function deleteOrder(orderId) {
            // แทนที่ด้วยโค้ดที่คุณต้องการให้ทำเมื่อคลิกปุ่ม "ลบ"
            console.log("ลบคำสั่งซื้อ: " + orderId);
            if (confirm("คุณต้องการลบคำสั่งซื้อหมายเลข: " + orderId + " จริงหรือไม่?")) {
                // หากผู้ใช้ยืนยันให้ลบ ทำการส่งคำขอไปลบที่ Server

                //ตัวอย่างการ redirect
                window.location.href = 'delete_order.php?id=' + orderId;
                alert("ทำการลบคำสั่งซื้อหมายเลข: " + orderId);
            }


        }
    </script>
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