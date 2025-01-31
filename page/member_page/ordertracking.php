<?php
session_start();

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูลหรือไม่
include '../php_script/db_connection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึง order_id จาก session
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
} else if (isset($_SESSION['order_id'])) {
    $order_id = $_SESSION['order_id'];
} else {
    echo "ไม่พบรหัสคำสั่งซื้อ";
    exit;
}
// ดึงข้อมูลสถานะ order จากฐานข้อมูล
$sql_status = "SELECT status FROM orders WHERE order_id = ?";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("i", $order_id);
$stmt_status->execute();
$result_status = $stmt_status->get_result();

if ($result_status->num_rows > 0) {
    $order_status = $result_status->fetch_assoc()['status'];
} else {
    $order_status = "ไม่พบข้อมูลสถานะ";
}

$stmt_status->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <div class="container mt-4">
        <h3 class="text-center" style="margin-top: 30px;line-height: 50px">รายละเอียดคำสั่งซื้อ</h3>
        <div class="progress-step">
            <div>ยืนยันคำสั่งซื้อ</div>
            <div>จัดเตรียมสินค้า</div>
            <div>อยู่ระหว่างส่งสินค้า</div>
            <div>เสร็จสิ้น</div>
        </div>
        <div class="progress-bar-custom">
            <div class="progress-bar-step"></div>
        </div>

        <?php
        // ดึงข้อมูลสินค้าจากฐานข้อมูล
        $sql = "
        SELECT 
            od.product_id,
            p.product_name AS name,
            od.amount AS quantity,
            p.price,
            (od.amount * p.price) AS subtotal
        FROM orderdetail od
        JOIN product p ON od.product_id = p.product_id
        WHERE od.order_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // คำนวณยอดรวมทั้งหมด
        $total = 0;
        $items = $result->fetch_all(MYSQLI_ASSOC);

        // เริ่มต้นการแสดงผล
        //step
        $step = 0;
        if ($order_status == "pending") {
            $step = 25;
        } else if ($order_status == "pickup") {
            $step = 50;
        } elseif ($order_status == "shipped") {
            $step = 75;
        } elseif ($order_status == "success") {
            $step = 100;
        }

        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const progressBarStep = document.querySelector('.progress-bar-step');
                let width = <?php echo $step ?>;
                progressBarStep.style.width = width + '%';
            });
        </script>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">รายการสินค้า</h5>
                <p>รหัสคำสั่งซื้อ: <?php echo htmlspecialchars($order_id); ?></p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อ</th>
                            <th>จำนวน</th>
                            <th>ราคา</th>
                            <th>ทั้งหมด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <?php $total += $item['subtotal']; ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">รายการรวมทั้งหมด</th>
                            <th><?php echo number_format($total, 2); ?> บาท</th>
                        </tr>
                        <tr>
                            <?php
                            $sql = "SELECT payment_method FROM payment WHERE order_id = ?";
                            $stmt_payment = $conn->prepare($sql);
                            $stmt_payment->bind_param("i", $order_id);
                            $stmt_payment->execute();
                            $result_payment = $stmt_payment->get_result();
                            $payment_method = "ไม่พบข้อมูล"; // ค่าเริ่มต้นกรณีไม่มีข้อมูล

                            if ($row = $result_payment->fetch_assoc()) {
                                $payment_method = $row['payment_method']; // เก็บค่าจากฐานข้อมูล
                            }

                            // ปิดการเชื่อมต่อ
                            $stmt_payment->close();
                            ?>
                            <th colspan="4">วิธีการชำระเงิน</th>
                            <th><?php if ($payment_method == "cod") {
                                    echo "ชำระปลายทาง";
                                } else {
                                    echo "ชำระเงินผ่านการโอน";
                                } ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลการจัดส่ง</h5>
                <?php
                $sql_delivery = "SELECT delivery_date, parcel_number, delivery_by FROM delivery WHERE order_id = ?";
                $stmt_delivery = $conn->prepare($sql_delivery);
                $stmt_delivery->bind_param("i", $order_id);
                $stmt_delivery->execute();
                $result_delivery = $stmt_delivery->get_result();
                if ($result_delivery->num_rows > 0) {
                    $delivery_data = $result_delivery->fetch_assoc();
                ?>
                    <p><strong>วันที่จัดส่ง:</strong> <?php echo htmlspecialchars($delivery_data['delivery_date']); ?></p>
                    <p><strong>หมายเลขพัสดุ:</strong> <?php echo htmlspecialchars($delivery_data['parcel_number']); ?></p>
                    <p><strong>จัดส่งโดย:</strong> <?php echo htmlspecialchars($delivery_data['delivery_by']); ?></p>

                <?php
                } else {
                ?>
                    <p>ยังไม่มีข้อมูลการจัดส่ง</p>
                <?php
                }
                $stmt_delivery->close();
                ?>
            </div>
        </div>
    </div>

    <!-- ฟอร์มสำหรับยืนยันการสั่งซื้อ (แสดงเมื่อสถานะเป็น 'wait' เท่านั้น) -->
    <?php if ($order_status == 'wait'): ?>
        <form id="update-status-form">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($order_status); ?>">

            <button type="submit" class="btn btn-primary mt-4 d-block mx-auto" style="max-width: 200px; padding: 10px 20px;">ยืนยันการสั่งซื้อ</button>
        </form>
        <div id="status-message"></div> <!-- สำหรับแสดงข้อความจาก backend -->
    <?php endif; ?>

    <button class="btn btn-secondary mt-4 w-100" onclick="window.location.href = 'myorder.php';">ย้อนกลับ</button>
    </div>

    <script>
        document.getElementById("update-status-form")?.addEventListener("submit", function(event) { // ใช้ optional chaining (?.) เพื่อป้องกัน error
            event.preventDefault();

            const formData = new FormData(this);

            fetch('update_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("status-message").innerHTML = `<p style="color: ${data.status === 'success' ? 'green' : (data.status === 'warning' ? 'orange' : 'red')}">${data.message}</p>`;

                    if (data.status === 'success') {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    document.getElementById("status-message").innerHTML = "<p style='color: red;'>An error occurred. Please try again.</p>";
                });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>

</html>

<script>
    function updateProgress(step) {
        const steps = document.querySelectorAll('.progress-step div');
        const progressBar = document.querySelector('.progress-bar-step');
        const totalSteps = steps.length;

        steps.forEach((el, index) => {
            el.classList.toggle('active', index < step);
        });

        const progressPercent = ((step - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = `${progressPercent}%`;
    }

    // กำหนดค่าขั้นตอนจาก PHP
    const currentStep = <?php echo $step; ?>;
    updateProgress(currentStep);


    function showLogoutPopup() {
        // Show the modal when the user clicks the logout link
        $('#logoutModal').modal('show');
    }
</script>
<style>
    .progress-step {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
    }

    .progress-step div {
        text-align: center;
    }

    .progress-bar-custom {
        width: 100%;
        height: 8px;
        background-color: #ddd;
        position: relative;
    }

    .progress-bar-step {
        width: 25%;
        height: 8px;
        background-color: #007bff;
        position: absolute;
    }

    .container.mt-4 {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        min-height: 400px;

        position: relative;
    }


    .btn.btn-secondary {
        width: auto;

        max-width: 120px;

        margin-top: 15px;

        display: block;
        margin-left: auto;
        margin-right: auto;

    }
</style>