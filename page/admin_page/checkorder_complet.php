<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบการชำระเงิน</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

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
        $sql_payment = "SELECT payment_id, payment_date, price, payment_method, payment_image,reference_number,bank FROM payment WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("i", $order_id);
        $stmt_payment->execute();
        $result_payment = $stmt_payment->get_result();
        $payment = $result_payment->fetch_assoc();

        // ดึงข้อมูลการจัดส่ง
        $sql_delivery = "SELECT delivery_date, parcel_number, delivery_by
        FROM delivery
        WHERE order_id = ? ORDER BY delivery_id DESC LIMIT 1";
        $stmt_delivery = $conn->prepare($sql_delivery);
        $stmt_delivery->bind_param("i", $order_id);
        $stmt_delivery->execute();
        $result_delivery = $stmt_delivery->get_result();
        $delivery = $result_delivery->fetch_assoc();
    } else {
        echo "<p>ไม่พบ order_id</p>";
        exit;
    }
    ?>

    <div class="content">
        <h2>รายละเอียดคำสั่งซื้อที่เสร็จสิ้นแล้ว</h2>
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
                            <td><?php {
                                    echo ("เสร็จสิ้น");
                                }


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
                <?php if ($payment['payment_method'] === "qr"): ?>
                    <h5 class="card-title">ข้อมูลการชำระเงิน</h5>
                    <form>
                        <div class="form-group d-flex align-items-center">
                            <label for="trackingNumber" style="margin-right: 10px;">หลักฐานการชำระเงิน</label>
                        </div>

                        <!-- รูปภาพแสดงด้านล่างของหลักฐานการชำระเงิน -->
                        <div class="form-group">
                            <?php if (!empty($payment['payment_image'])) : ?>
                                <img id="imagePreview" src="payment_image/<?php echo $payment['payment_image']; ?>" alt="Image Preview" style="max-width: 200px; margin-top: 10px; cursor: pointer;" onclick="viewImage()">
                            <?php else : ?>
                                <p>ยังไม่มีหลักฐานการชำระเงิน</p>
                            <?php endif; ?>
                        </div>

                        <!-- เพิ่มส่วนแสดง bank -->
                        <div class="form-group">
                            <label for="bank" style="margin-right: 10px;">ธนาคาร:</label>
                            <?php if (!empty($payment['bank'])) : ?>
                                <p id="bank"><?php echo $payment['bank']; ?></p>
                            <?php else : ?>
                                <p>ไม่มีข้อมูลธนาคาร</p>
                            <?php endif; ?>
                        </div>

                        <!-- เพิ่มส่วนแสดง reference_number -->
                        <div class="form-group">
                            <label for="reference_number" style="margin-right: 10px;">หมายเลขอ้างอิง:</label>
                            <?php if (!empty($payment['reference_number'])) : ?>
                                <p id="reference_number"><?php echo $payment['reference_number']; ?></p>
                            <?php else : ?>
                                <p>ไม่มีข้อมูลหมายเลขอ้างอิง</p>
                            <?php endif; ?>
                        </div>

                        <!-- เพิ่มส่วนแสดง payment_date -->
                        <div class="form-group mt-3">
                            <label for="payment_date" style="margin-right: 10px;">วันที่ชำระเงิน:</label>
                            <?php if (!empty($payment['payment_date'])) : ?>
                                <p id="payment_date"><?php echo $payment['payment_date']; ?></p>
                            <?php else : ?>
                                <p>ไม่มีข้อมูลวันที่ชำระเงิน</p>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>
                        <?php if ($_GET['status'] == 'shipped' || $_GET['status'] == 'success') : ?>
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h5 class="card-title">รายละเอียดการจัดส่ง</h5>
                                    <p>จัดส่งโดย : <?php echo isset($delivery['delivery_by']) ? htmlspecialchars($delivery['delivery_by']) : 'ไม่ระบุ'; ?></p>
                                    <p>หมายเลขพัสดุ : <?php echo isset($delivery['parcel_number']) ? htmlspecialchars($delivery['parcel_number']) : 'ไม่ระบุ'; ?></p>
                                    <p>วันที่จัดส่ง : <?php echo isset($delivery['delivery_date']) ? htmlspecialchars(date("d/m/Y", strtotime($delivery['delivery_date']))) : 'ไม่ระบุ'; ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <button type="button" class="btn btn-secondary mt-3 w-100" onclick="window.history.back()">ย้อนกลับ</button>

            </div>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function viewImage() {
            // Implement your image viewing logic here (e.g., using a modal)
            alert("You clicked the image! You can add modal/lightbox logic here.");
        }
    </script>
</body>

</html>
<!-- เพิ่ม JavaScript -->
<script>
    // ฟังก์ชันแสดงตัวอย่างรูปภาพ
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.src = reader.result;
            imagePreview.style.display = 'inline'; // แสดงรูปตัวอย่าง
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // ฟังก์ชันให้คลิกภาพเพื่อดูรูปใหญ่
    function viewImage() {
        const imagePreview = document.getElementById('imagePreview');
        const fullImage = window.open(imagePreview.src, '_blank');
        fullImage.document.write('<img src="' + imagePreview.src + '" style="width: 100%;">');
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