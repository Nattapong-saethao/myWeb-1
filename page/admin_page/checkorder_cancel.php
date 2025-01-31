<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบการยกเลิกคำสั่งซื้อ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content {
            margin-left: 250px;
            /* ระยะห่างจาก sidebar */
            padding: 20px;
            /* เพิ่ม padding ให้เนื้อหา */
        }
    </style>
</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    include '../php_script/db_connection.php';

    // ตรวจสอบว่ามี order_id ใน $_GET หรือไม่
    if (isset($_GET['id'])) {
        $order_id = $_GET['id'];
        $_POST['status'] = $_GET['status'];

        // ดึงข้อมูลคำสั่งซื้อ
        $sql_order = "SELECT o.order_id, o.order_date, o.status, m.username, m.surname, m.phone_number, m.address
    FROM orders o
    JOIN member m ON o.member_id = m.member_id
    WHERE o.order_id = ?";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        $order = $result_order->fetch_assoc();

        // ดึงรายการสินค้าในคำสั่งซื้อ
        $sql_order_detail = "SELECT od.amount, p.product_name 
                     FROM orderdetail od
                     JOIN product p ON od.product_id = p.product_id
                     WHERE od.order_id = ?";
        $stmt_order_detail = $conn->prepare($sql_order_detail);
        $stmt_order_detail->bind_param("i", $order_id);
        $stmt_order_detail->execute();
        $result_order_detail = $stmt_order_detail->get_result();
        $order_details = [];
        while ($row = $result_order_detail->fetch_assoc()) {
            $order_details[] = $row;
        }

        // ดึงข้อมูลการชำระเงิน
        $sql_payment = "SELECT payment_id, payment_date, price, payment_method, payment_image FROM payment WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("i", $order_id);
        $stmt_payment->execute();
        $result_payment = $stmt_payment->get_result();
        $payment = $result_payment->fetch_assoc();

        // ดึงข้อมูลการยกเลิก (ถ้ามี)
        $sql_cancel = "SELECT cancel_detail, cancel_date FROM cancel WHERE order_id = ?";
        $stmt_cancel = $conn->prepare($sql_cancel);
        $stmt_cancel->bind_param("i", $order_id);
        $stmt_cancel->execute();
        $result_cancel = $stmt_cancel->get_result();
        $cancel = $result_cancel->fetch_assoc();
    } else {
        echo "<p>ไม่พบ order_id</p>";
        exit;
    }
    ?>

    <div class="content">
        <h2>รายละเอียดคำสั่งซื้อที่ถูกยกเลิก</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลลูกค้า</h5>
                <p>ชื่อ : <?php echo $order['username'] . " " . $order['surname']; ?></p>
                <p>เบอร์โทรศัพท์ : <?php echo $order['phone_number']; ?></p>
                <p>ที่อยู่จัดส่ง : <?php echo $order['address']; ?></p>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลคำสั่งซื้อ</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>คำสั่งซื้อ</th>
                            <th>รายการสินค้า</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th>วิธีการชำระเงิน</th>
                            <th>สถานะคำสั่งซื้อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td>
                                <?php
                                $items = array();
                                foreach ($order_details as $item) {
                                    $items[] = $item['product_name'] . " (" . $item['amount'] . ")";
                                }
                                echo implode(", ", $items);
                                ?>
                            </td>
                            <td><?php echo date("d/m/Y", strtotime($order['order_date'])); ?></td>
                            <td><?php if ($payment['payment_method'] === "cod") {
                                    echo "ชำระปลายทาง";
                                } else if ($payment['payment_method'] === "qr") {
                                    echo "ชำระผ่านการโอนเงิน";
                                } else {
                                    echo 'ยังไม่ได้ชำระ';
                                }
                                ?></td>
                            <td><?php
                                echo ("ยกเลิกแล้ว");

                                ?></td>
                        </tr>
                        <tr>
                            <td>ยอดเงินที่ต้องชำระเงิน : <?php echo number_format($payment['price'] ?? '0.00', 2) ?> บาท</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลการยกเลิก</h5>

                <form>
                    <!-- แสดงรายละเอียดการยกเลิก -->
                    <div class="form-group">
                        <label for="cancel_detail" style="margin-right: 10px;">รายละเอียดการยกเลิก:</label>
                        <?php if (!empty($cancel['cancel_detail'])) : ?>
                            <p id="cancel_detail"><?php echo htmlspecialchars($cancel['cancel_detail']); ?></p>
                        <?php else : ?>
                            <p>ไม่มีรายละเอียดการยกเลิก</p>
                        <?php endif; ?>
                    </div>

                    <!-- แสดงวันที่ยกเลิก -->
                    <div class="form-group">
                        <label for="cancel_date" style="margin-right: 10px;">วันที่ยกเลิก:</label>
                        <?php if (!empty($cancel['cancel_date'])) : ?>
                            <p id="cancel_date"><?php echo htmlspecialchars($cancel['cancel_date']); ?></p>
                        <?php else : ?>
                            <p>ไม่มีข้อมูลวันที่ยกเลิก</p>
                        <?php endif; ?>
                    </div>

                </form>


                <button type="button" class="btn btn-secondary mt-3" onclick="window.history.back()">ย้อนกลับ</button>


            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
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
</style>