<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดเตรียมและจัดส่งสินค้า</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #f8f9fa;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar a {
            padding: 10px 20px;
            display: block;
            text-decoration: none;
            color: #333;
        }

        .sidebar a:hover {
            background-color: #e9ecef;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    include '../php_script/db_connection.php';

    // ตรวจสอบว่ามี order_id ใน $_GET หรือไม่
    if (isset($_GET['id']) && isset($_GET['status'])) {
        $order_id = $_GET['id'];
        $status = $_GET['status'];
        $user_id = $_SESSION['user_id'];

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
        // ดึงข้อมูลการชำระเงิน (แก้ไขส่วนนี้)
        $sql_payment = "SELECT payment_id, member_id, order_id, payment_date, price, payment_method, payment_image, reference_number, bank
        FROM payment 
        WHERE order_id = ?
        ORDER BY payment_id DESC
        LIMIT 1";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("i", $order_id);
        $stmt_payment->execute();
        $result_payment = $stmt_payment->get_result();
        $payment = $result_payment->fetch_assoc();

        // ดึงข้อมูลการจัดส่ง
        $sql_delivery = "SELECT delivery_id, user_id, order_id, delivery_date, parcel_number, delivery_by, delivery_address
                        FROM delivery
                        WHERE order_id = ?";
        $stmt_delivery = $conn->prepare($sql_delivery);
        $stmt_delivery->bind_param("i", $order_id);
        $stmt_delivery->execute();
        $result_delivery = $stmt_delivery->get_result();
        $delivery = $result_delivery->fetch_assoc();
    } else {
        echo "<p>ไม่พบ order_id หรือ status</p>";
        exit;
    }

    // เมื่อมีการกดปุ่มถัดไป
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $order_status = $_POST['orderStatus'];

        // อัปเดตสถานะคำสั่งซื้อ
        try {
            $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $order_status, $order_id);
            if ($stmt_update->execute()) {


                echo "<p>แก้ไขข้อมูลคำสั่งซื้อสำเร็จ</p>";
                if ($status == 'pending') {
                    header("Location: order.php");
                } elseif ($status == 'pickup') {
                    header("Location: order.php");
                } elseif ($status == 'shipped') {
                    header("Location: order.php");
                }

                exit;
            } else {
                throw new Exception("Error updating orders: " . $stmt_update->error);
            }
            $stmt_update->close();
        } catch (Exception $e) {
            echo "<p>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
        }
    }
    ?>
    <div class="content">
        <h2>รายละเอียดคำสั่งซื้อที่อยู่รหว่างจัดส่งสินค้า</h2>
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
                            <th class="text-center">คำสั่งซื้อ</th>
                            <th class="text-center">รายการสินค้า</th>
                            <th class="text-center">วันที่สั่งซื้อ</th>
                            <th class="text-center">สถานะการชำระเงิน</th>
                            <th class="text-center">สถานะคำสั่งซื้อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><?php echo $order['order_id']; ?></td>
                            <td class="text-center">
                                <?php
                                $items = array();
                                foreach ($order_details as $item) {
                                    $items[] = $item['product_name'] . " (" . $item['amount'] . ")";
                                }
                                echo implode(", ", $items);
                                ?>
                            </td>
                            <td class="text-center"><?php echo date("d/m/Y", strtotime($order['order_date'])); ?></td>
                            <td class="text-center">
                                <?php if ($payment) {
                                    if ($payment['payment_method'] === "cod") {
                                        echo "ชำระปลายทาง";
                                    } else if ($payment['payment_method'] === "qr") {
                                        echo "ชำระผ่านการโอนเงิน";
                                    }
                                } else {
                                    echo "ยังไม่ได้ชำระ";
                                }

                                ?>
                            </td>
                            <td class="text-center">
                                <?php
                                if ($order['status'] == 'pending') {
                                    echo "รอตรวจสอบ";
                                } else if ($order['status'] == 'pickup') {
                                    echo "จัดเตรียมสินค้า";
                                } else if ($order['status'] == 'shipped') {
                                    echo "อยู่ระหว่างจัดส่ง";
                                } else if ($order['status'] == 'success') {
                                    echo "เสร็จสิ้น";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">ข้อมูลการชำระเงิน</h5>
                <?php if ($payment): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>วันที่ชำระเงิน:</strong> <?php echo htmlspecialchars($payment['payment_date']); ?></p>
                            <p><strong>ราคาที่ต้องชำระ:</strong> <?php echo htmlspecialchars(number_format($payment['price'], 2)); ?> บาท</p>
                            <p><strong>วิธีชำระเงิน:</strong> <?php if ($payment['payment_method'] == 'cod') echo "ชำระปลายทาง";
                                                                else echo "ชำระผ่านการโอนเงิน"; ?></p>

                        </div>
                        <div class="col-md-6">

                            <?php if ($payment['payment_image']): ?>
                                <p><strong>รูปหลักฐานการชำระเงิน:</strong> <br> <img src="<?php echo htmlspecialchars($payment['payment_image']); ?>" class="img-fluid" style="max-height: 150px;"></p>
                            <?php endif; ?>
                            <p><strong>หมายเลขอ้างอิง:</strong> <?php echo htmlspecialchars($payment['reference_number']); ?></p>
                            <p><strong>ธนาคาร:</strong> <?php echo htmlspecialchars($payment['bank']); ?></p>
                        </div>
                    </div>

                <?php else: ?>
                    <p class="text-muted">ยังไม่มีข้อมูลการชำระเงิน</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">ข้อมูลการจัดส่ง</h5>
                <?php if ($delivery): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>จัดส่งโดย:</strong> <?php echo htmlspecialchars($delivery['delivery_by']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>หมายเลขพัสดุ:</strong> <?php echo htmlspecialchars($delivery['parcel_number']); ?></p>
                            <p><strong>วันที่จัดส่ง:</strong> <?php echo date("d/m/Y", strtotime($delivery['delivery_date'])); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">ยังไม่มีข้อมูลการจัดส่ง</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body text-center">
            <button type="button" class="btn btn-primary" onclick="window.history.back()">ย้อนกลับ</button>
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