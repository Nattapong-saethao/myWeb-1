<!DOCTYPE html>
<html lang="en">
<?php
session_start();

?>

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
        // เชื่อมต่อฐานข้อมูล
        include '../php_script/db_connection.php';
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $_SESSION['order_id'] = $_POST['order_id'];
        //var_dump($_GET['order_id'], $_SESSION['order_id'], $_POST['order_id']);
        // ตรวจสอบว่ามี order_id ใน session หรือไม่
        if (isset($_SESSION['order_id'])) {
            $order_id = $_SESSION['order_id'];
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
            } elseif ($order_status == "wait") {
                $step = 1;
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
            <!-- ฟอร์มสำหรับยืนยันการสั่งซื้อ (แสดงเมื่อสถานะเป็น 'wait' เท่านั้น) -->
            <?php if ($order_status == 'wait'): ?>
                <form id="update-status-form">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($order_status); ?>">

                    <button type="submit" class="btn btn-primary mt-4 w-100">ยืนยันการสั่งซื้อ</button>
                </form>
                <div id="status-message"></div> <!-- สำหรับแสดงข้อความจาก backend -->
            <?php endif; ?>
            <button type="button" class="btn btn-secondary mt-4 w-100" onclick="window.location.href = 'myorder.php';">กลับหน้าหลัก</button>
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
        <?php
        } else {
            echo "<p>ไม่พบข้อมูล Order โปรดทำรายการใหม่อีกครั้ง</p>";
            echo "<button class='btn btn-secondary mt-4 w-50' onclick='window.location.href = \"checkout.php\";'>กลับไปหน้า Checkout</button>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

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
        width: 45%;
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
        /* ให้พื้นที่แนวตั้งอย่างน้อย */
    }

    .container.mt-4 {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        min-height: 400px;
        /* ให้พื้นที่แนวตั้งอย่างน้อย */
        position: relative;
    }

    .btn.btn-primary {
        width: auto;
        /* ให้ปุ่มมีขนาดตามเนื้อหาภายใน */
        max-width: 300px;
        /* กำหนดความกว้างสูงสุด */
        margin-top: 15px;
        /* ให้มีระยะห่างจากข้อมูลที่อยู่ด้านบน */
        display: block;
        margin-left: auto;
        margin-right: auto;
        /* จัดกึ่งกลาง */
    }

    .btn.btn-secondary {
        width: auto;
        /* ให้ปุ่มมีขนาดตามเนื้อหาภายใน */
        max-width: 120px;
        /* กำหนดความกว้างสูงสุด */
        margin-top: 15px;
        /* ให้มีระยะห่างจากข้อมูลที่อยู่ด้านบน */
        display: block;
        margin-left: auto;
        margin-right: auto;
        /* จัดกึ่งกลาง */
    }
</style>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $order_id = $_POST['order_id'];
    $transfer_date = $_POST['transfer_date'];
    $transfer_time = $_POST['transfer_time'];
    $bank = $_POST['bank'];
    $reference_number = $_POST['reference_number'];

    //   จัดการไฟล์ upload
    $targetDir = "D:/xampp/htdocs/myWeb/page/admin_page/payment_image/"; // โฟลเดอร์สำหรับเก็บไฟล์

    // Check for upload errors
    if ($_FILES["payment_slip"]["error"] != UPLOAD_ERR_OK) {
        echo "<script>alert('Error uploading file: " . $_FILES["payment_slip"]["error"] . "')</script>";
        goto end_script; // ออกจากส่วน upload ทันที
    }

    // Generate a unique filename
    $file_extension = strtolower(pathinfo($_FILES["payment_slip"]["name"], PATHINFO_EXTENSION));
    $file_name = uniqid() . "." . $file_extension;
    $targetFile = $targetDir . $file_name;

    // ตรวจสอบชนิดของไฟล์
    $allowed_types = array("jpg", "png", "jpeg", "pdf");

    if (in_array($file_extension, $allowed_types)) {


        if (move_uploaded_file($_FILES["payment_slip"]["tmp_name"], $targetFile)) {

            // ถ้าอัปโหลดไฟล์สำเร็จ
            $sql_check_payment = "SELECT payment_id FROM payment WHERE order_id = ?";
            $stmt_check_payment = $conn->prepare($sql_check_payment);
            $stmt_check_payment->bind_param("i", $order_id);
            $stmt_check_payment->execute();
            $result_check_payment = $stmt_check_payment->get_result();

            //แปลงวันที่และเวลา
            $payment_datetime = $transfer_date . ' ' . $transfer_time;
            if ($result_check_payment->num_rows > 0) {
                // อัปเดตข้อมูล payment ที่มีอยู่แล้ว
                $sql_update = "UPDATE payment SET payment_date = ?, payment_image = ?, bank = ?, reference_number = ? WHERE order_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssi", $payment_datetime, $file_name, $bank, $reference_number, $order_id);
                if ($stmt_update->execute()) {
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูลการชำระเงิน: " . $stmt_update->error . "')</script>";
                }
                $stmt_update->close();
            } else {
                // เพิ่มข้อมูล payment ใหม่
                $sql_insert = "INSERT INTO payment (member_id, order_id, payment_date, price, payment_method, payment_image, bank, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iisdssss", $_SESSION['member_id'], $order_id, $payment_datetime, $_SESSION['total_price'], $_SESSION['payment_method'], $file_name, $bank, $reference_number);
                if ($stmt_insert->execute()) {
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มข้อมูลการชำระเงิน: " . $stmt_insert->error . "')</script>";
                }
                $stmt_insert->close();
            }
            $stmt_check_payment->close();
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการ move ไฟล์อัปโหลดไปยังโฟลเดอร์')</script>";
        }
    } else {
        echo  "<script>alert('ชนิดไฟล์ไม่ถูกต้อง กรุณาอัปโหลดไฟล์ JPG, PNG, JPEG หรือ PDF เท่านั้น')</script>";
    }
}
end_script:
// ปิดการเชื่อมต่อ
if (isset($conn)) {
    $conn->close();
}
?>