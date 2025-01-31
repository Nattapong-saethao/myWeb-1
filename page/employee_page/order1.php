<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดเตรียมและจัดส่งสินค้า</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    } else {
        echo "<p>ไม่พบ order_id หรือ status</p>";
        exit;
    }

    // เมื่อมีการกดปุ่มถัดไป
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $shipping_by = $_POST['shippingBy'];
        $tracking_number = $_POST['trackingNumber'];
        $order_status = $_POST['orderStatus'];

        // อัปเดตสถานะคำสั่งซื้อ และเพิ่มข้อมูลการจัดส่ง
        try {
            $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $order_status, $order_id);
            if ($stmt_update->execute()) {

                // ตรวจสอบว่ามีข้อมูล delivery หรือยัง
                $sql_check_delivery = "SELECT delivery_id FROM delivery WHERE order_id = ?";
                $stmt_check_delivery = $conn->prepare($sql_check_delivery);
                $stmt_check_delivery->bind_param("i", $order_id);
                $stmt_check_delivery->execute();
                $result_check_delivery = $stmt_check_delivery->get_result();

                if ($result_check_delivery->num_rows > 0) {
                    // ถ้ามี ให้ทำการอัปเดต
                    $sql_delivery = "UPDATE delivery SET delivery_by = ?, parcel_number = ? WHERE order_id = ?";
                    $stmt_delivery = $conn->prepare($sql_delivery);
                    $stmt_delivery->bind_param("ssi", $shipping_by, $tracking_number, $order_id);

                    if (!$stmt_delivery->execute()) {
                        throw new Exception("Error updating delivery: " . $stmt_delivery->error);
                    }
                } else {
                    // ถ้ายังไม่มี ให้ทำการเพิ่มข้อมูล
                    $sql_delivery = "INSERT INTO delivery (user_id,order_id, delivery_by, parcel_number) VALUES (?, ?, ?, ?)";
                    $stmt_delivery = $conn->prepare($sql_delivery);
                    $stmt_delivery->bind_param("iiss", $user_id, $order_id, $shipping_by, $tracking_number);
                    if (!$stmt_delivery->execute()) {
                        throw new Exception("Error inserting into delivery: " . $stmt_delivery->error);
                    }
                }
                echo "<p>แก้ไขข้อมูลคำสั่งซื้อสำเร็จ</p>";
                if ($status == 'pending') {
                    header("Location: order.php");
                } elseif ($status == 'pickup') {
                    header("Location: order.php");
                } elseif ($status == 'shipped') {
                    header("Location: order.php");
                }

                exit;

                $stmt_check_delivery->close();
                $stmt_delivery->close();
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
        <h2>จัดเตรียมและจัดส่งสินค้า</h2>
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
            <div class="card mt-4">
                <h5 class="card-title">ข้อมูลการชำระเงิน</h5>
                <div class="card-body">
                    <?php if ($payment): ?>
                        <p><strong>วันที่ชำระเงิน:</strong> <?php echo htmlspecialchars($payment['payment_date']); ?></p>
                        <p><strong>ราคาที่ต้องชำระ:</strong> <?php echo htmlspecialchars(number_format($payment['price'], 2)); ?></p>
                        <p><strong>วิธีชำระเงิน:</strong> <?php if ($payment['payment_method'] == 'cod') echo "ชำระปลายทาง";
                                                            else echo "ชำระผ่านการโอนเงิน"; ?></p>
                        <?php if ($payment['payment_image']): ?>
                            <p><strong>รูปหลักฐานการชำระเงิน:</strong> <img src="<?php echo htmlspecialchars($payment['payment_image']); ?>" width="100"></p>
                        <?php endif; ?>
                        <p><strong>หมายเลขอ้างอิง:</strong> <?php echo htmlspecialchars($payment['reference_number']); ?></p>
                        <p><strong>Bank:</strong> <?php echo htmlspecialchars($payment['bank']); ?></p>
                    <?php else: ?>
                        <p>ยังไม่มีข้อมูลการชำระเงิน</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">ข้อมูลจัดส่ง</h5>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <div class="form-group">
                        <label for="shippingBy">จัดส่งโดย</label>
                        <input type="text" class="form-control" id="shippingBy" name="shippingBy" style="max-width: 350px;" required>
                    </div>
                    <div class="form-group">
                        <label for="trackingNumber">หมายเลขพัสดุ</label>
                        <input type="text" class="form-control" id="trackingNumber" name="trackingNumber" style="max-width: 350px;" required>
                    </div>
                    <div class="form-group">
                        <label for="orderStatus">สถานะคำสั่งซื้อ</label>
                        <select class="form-control" id="orderStatus" name="orderStatus" style="max-width: 350px;" required>
                            <option value="pickup">จัดเตรียมสินค้า</option>
                            <option value="shipped">อยู่ระหว่างจัดส่ง</option>
                            <option value="success">เสร็จสิ้น</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update">บันทึก</button>
                </form>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

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