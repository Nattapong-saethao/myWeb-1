<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการคำสั่งซื้อ</title>
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

    // ดึงข้อมูลคำสั่งซื้อจากฐานข้อมูล
    try {
        $sql = "SELECT o.order_id, o.order_date, o.status, m.username, m.surname, m.address
        FROM orders o
        JOIN member m ON o.member_id = m.member_id
        WHERE o.status = 'pickup'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }
    ?>
    <div class="content">
        <h2>รายการคำสั่งซื้อที่รอจัดเตรียม</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th class="text-center">คำสั่งซื้อ/Order</th>
                    <th class="text-center">รายการสินค้า/Product list</th>
                    <th class="text-center">ชื่อ/Name</th>
                    <th class="text-center">ที่อยู่/Address</th>
                    <th class="text-center">วันที่สั่งซื้อ/Order date</th>
                    <th class="text-center">วิธีการชำระเงิน</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders) : ?>
                    <?php foreach ($orders as $order) : ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td class="text-center">
                                <?php
                                // ดึงรายการสินค้าในคำสั่งซื้อ พร้อมหน่วยนับ
                                $sql_order_detail = "SELECT od.amount, p.product_name, u.unit_name
                                                     FROM orderdetail od
                                                     JOIN product p ON od.product_id = p.product_id
                                                     JOIN units u ON p.unit_id = u.unit_id
                                                     WHERE od.order_id = ?";
                                $stmt_order_detail = $conn->prepare($sql_order_detail);
                                $stmt_order_detail->bind_param("i", $order['order_id']);
                                $stmt_order_detail->execute();
                                $result_order_detail = $stmt_order_detail->get_result();
                                $order_details = [];
                                while ($row = $result_order_detail->fetch_assoc()) {
                                    $order_details[] = $row['product_name'] . " (" . $row['amount'] . " " . $row['unit_name'] . ")";
                                }
                                echo implode(", ", $order_details);
                                $stmt_order_detail->close();
                                ?>
                            </td>
                            <td class="text-center"><?php echo htmlspecialchars($order['username']) . " " . htmlspecialchars($order['surname']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($order['address']); ?></td>
                            <td class="text-center"><?php echo date("d/m/Y", strtotime($order['order_date'])); ?></td>
                            <td class="text-center">
                                <?php
                                // ดึงข้อมูลการชำระเงิน
                                $sql_payment = "SELECT payment_method FROM payment WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1";
                                $stmt_payment = $conn->prepare($sql_payment);
                                $stmt_payment->bind_param("i", $order['order_id']);
                                $stmt_payment->execute();
                                $result_payment = $stmt_payment->get_result();
                                $payment = $result_payment->fetch_assoc();
                                if ($payment) {
                                    if ($payment['payment_method'] === "cod") {
                                        echo "ชำระปลายทาง";
                                    } else if ($payment['payment_method'] === "qr") {
                                        echo "ชำระผ่านการโอนเงิน";
                                    }
                                } else {
                                    echo "ยังไม่ได้ชำระ";
                                }
                                $stmt_payment->close();
                                ?>
                            </td>
                            <td class="text-center">
                                <form method="GET" action="order1.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $order['status']; ?>">
                                    <button class="btn btn-primary">จัดเตรียม</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center">ไม่พบคำสั่งซื้อ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<script>
    function goToDetail() {
        window.location.href = 'order_detail.php';
    }
</script>
<script>
    function goToDetail() {
        window.location.href = 'order1.php';
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
        background-color: #f8f9fa;
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
</style>