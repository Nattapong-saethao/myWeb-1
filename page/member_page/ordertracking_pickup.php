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
    session_start();
    include 'navbar.php';
    ?>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = $_POST['order_id'] ?? null;
        $total_price = $_POST['total_price'] ?? 0.00;
    } else {
        $order_id = null;
        $total_price = 0.00;
    }
    ?>
    <div class="container mt-4">
        <h3 class="text-center" style="margin-top: 30px;line-height: 50px">สถานะการสั่งซื้อ</h3>
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
        ?>
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
                            <th colspan="4">วิธีการชำระเงิน</th>
                            <th>ชำระปลายทาง</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <button class="btn btn-secondary mt-4 w-100" onclick="window.location.href = 'home.php';">กลับหน้าหลัก</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<script>
    function updateProgress(step) {
        const steps = document.querySelectorAll('.progress-step .step');
        const progressBar = document.querySelector('.progress-bar-step');
        const totalSteps = steps.length;

        steps.forEach((el, index) => {
            el.classList.toggle('active', index < step);
        });

        const progressPercent = ((step - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = `${progressPercent}%`;
    }

    // เรียกใช้งาน updateProgress โดยกำหนดขั้นตอนที่ต้องการ
    updateProgress(2); // ตัวอย่าง: ขั้นตอนที่ 2 (จัดเตรียมสินค้า)

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
        width: 50%;
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