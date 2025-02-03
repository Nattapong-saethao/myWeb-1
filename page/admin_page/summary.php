<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานยอดขาย</title>
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
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <?php
                session_start();
                include('sidebar.php');
                include '../php_script/db_connection.php';
                ?>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 content">
                <h2>รายงานยอดขาย</h2>
                <p>ข้อมูลยอดขาย ณ วันที่ <?php echo date('d/m/Y'); ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <?php
                            // ดึงประเภทสินค้าทั้งหมด
                            $sql_categories = "SELECT category_id, category_name FROM productcategory";
                            $stmt_categories = $conn->prepare($sql_categories);
                            $stmt_categories->execute();
                            $result_categories = $stmt_categories->get_result();

                            while ($category = $result_categories->fetch_assoc()) {
                                $category_id = $category['category_id'];
                                $category_name = $category['category_name'];

                                // ดึงยอดขายสำหรับแต่ละประเภทสินค้า (เฉพาะ order ที่สำเร็จ)
                                $sql_count = "SELECT COUNT(*) AS total_count
                    FROM orderdetail od
                    JOIN product p ON od.product_id = p.product_id
                    JOIN orders o ON od.order_id = o.order_id
                    WHERE p.category_id = ? AND o.status = 'success'";
                                $stmt_count = $conn->prepare($sql_count);
                                $stmt_count->bind_param("i", $category_id);
                                $stmt_count->execute();
                                $result_count = $stmt_count->get_result();
                                $count_data = $result_count->fetch_assoc();
                                $count = $count_data['total_count'] ?? 0;
                            ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                            <h5 class="card-title mb-3 text-center">ยอดขาย <?php echo htmlspecialchars($category_name); ?></h5>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-4"><?php echo number_format($count); ?></span><span class="ms-2 fs-6"> ชิ้น</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>

                        <style>
                            .card {
                                border-radius: 10px;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                            }

                            .card-title {
                                font-weight: bold;
                            }

                            .badge {
                                padding: 0.5em 0.75em;
                            }

                            .bg-primary {
                                background-color: #0d6efd !important;
                            }

                            .fs-4 {
                                font-size: 1.5rem !important;
                            }

                            .fs-6 {
                                font-size: 1rem !important;
                            }

                            .ms-2 {
                                margin-left: 0.5rem !important;
                            }
                        </style>
                        <div class="card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <h5 class="card-title mb-3">ยอดคำสั่งซื้อที่เสร็จสิ้น</h5>
                                <?php
                                $sql_success_orders = "SELECT COUNT(*) as success_order_count FROM orders WHERE status = 'success'";
                                $stmt_success = $conn->prepare($sql_success_orders);
                                $stmt_success->execute();
                                $result_success = $stmt_success->get_result();
                                $success_orders = $result_success->fetch_assoc();

                                $success_count = $success_orders['success_order_count'] ?? 0;

                                ?>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success fs-4"><?php echo number_format($success_count); ?></span><span class="ms-2 fs-6"> รายการ</span>
                                </div>
                            </div>
                        </div>

                        <style>
                            .card {
                                border-radius: 10px;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                            }

                            .card-title {
                                font-weight: bold;
                            }

                            .badge {
                                padding: 0.5em 0.75em;
                            }

                            .bg-success {
                                background-color: #198754 !important;
                            }

                            .fs-4 {
                                font-size: 1.5rem !important;
                            }

                            .fs-6 {
                                font-size: 1rem !important;
                            }

                            .ms-2 {
                                margin-left: 0.5rem !important;
                            }
                        </style>
                        <div class="card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <h5 class="card-title mb-3">ยอดคำสั่งซื้อที่ถูกยกเลิก</h5>
                                <?php
                                $sql_canceled_orders = "SELECT COUNT(*) as cancel_order_count FROM cancel";
                                $stmt_cancel = $conn->prepare($sql_canceled_orders);
                                $stmt_cancel->execute();
                                $result_cancel = $stmt_cancel->get_result();
                                $cancel_orders = $result_cancel->fetch_assoc();

                                $cancel_count = $cancel_orders['cancel_order_count'] ?? 0;
                                ?>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger fs-4"><?php echo number_format($cancel_count); ?></span><span class="ms-2 fs-6"> รายการ</span>
                                </div>
                            </div>
                        </div>

                        <style>
                            .card {
                                border-radius: 10px;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                            }

                            .card-title {
                                font-weight: bold;
                            }

                            .badge {
                                padding: 0.5em 0.75em;
                            }

                            .bg-danger {
                                background-color: #dc3545 !important;
                            }
                        </style>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">คำสั่งซื้อต่อวัน</h5>
                                <div class="mb-3">
                                    <label for="selectDate" class="form-label">เลือกวันที่</label>
                                    <input type="date" id="selectDate" class="form-control" style="max-width: 200px;" onchange="updateOrders()">
                                </div>
                                <div class="mt-4" id="orderSummary">
                                    <?php
                                    $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

                                    $sql_day_orders = "SELECT COUNT(*) as day_order_count, SUM(od.price * od.amount) as total_sales
                                FROM orders o
                                JOIN orderdetail od ON o.order_id = od.order_id
                                WHERE DATE(o.order_date) = ? AND o.status = 'success'";
                                    $stmt_day = $conn->prepare($sql_day_orders);
                                    $stmt_day->bind_param("s", $selected_date);
                                    $stmt_day->execute();
                                    $result_day = $stmt_day->get_result();
                                    $day_data = $result_day->fetch_assoc();

                                    $day_order_count = $day_data['day_order_count'] ?? 0;
                                    $total_day_sales = $day_data['total_sales'] ?? 0;

                                    echo "<h6 class='mb-3'>ข้อมูลสำหรับวันที่ <span class='text-primary'>" . date('d/m/Y', strtotime($selected_date)) . "</span></h6>";
                                    echo "<p>จำนวนคำสั่งซื้อที่สำเร็จ: <span class='badge bg-secondary fs-6'>" . number_format($day_order_count) . "</span> รายการ</p>";
                                    echo "<p>ยอดขายรวม: <span class='badge bg-success fs-6'>" . number_format($total_day_sales, 2) . "</span> บาท</p>";
                                    ?>
                                </div>
                            </div>
                        </div>



                        <script>
                            function updateOrders() {
                                var selectedDate = document.getElementById('selectDate').value;
                                var url = window.location.href.split('?')[0]; // Get base URL
                                window.location.href = url + '?date=' + selectedDate; // Redirect with date
                            }
                        </script>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">คำสั่งซื้อต่อเดือน</h5>
                                <form method="GET" class="mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <label for="month" class="form-label">เดือน</label>
                                            <select class="form-select" id="month" name="month">
                                                <?php
                                                for ($month = 1; $month <= 12; $month++) {
                                                    $monthName = date('F', mktime(0, 0, 0, $month, 1));
                                                    $selected = (isset($_GET['month']) && $_GET['month'] == $month) ? 'selected' : (date('n') == $month ? 'selected' : '');
                                                    echo "<option value=\"$month\" $selected>$monthName</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="year" class="form-label">ปี</label>
                                            <select class="form-select" id="year" name="year">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($year = 2023; $year <= $currentYear + 1; $year++) {
                                                    $selected = (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : ($year == $currentYear ? 'selected' : '');
                                                    echo "<option value=\"$year\" $selected>$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-primary w-100">กรองข้อมูล</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="mt-4">
                                    <?php
                                    $selected_month = $_GET['month'] ?? date('n');
                                    $selected_year = $_GET['year'] ?? date('Y');

                                    $sql_month_orders = "SELECT COUNT(*) as month_order_count, SUM(od.price * od.amount) as total_sales
                FROM orders o
                JOIN orderdetail od ON o.order_id = od.order_id
                WHERE MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? AND o.status = 'success'";
                                    $stmt_month = $conn->prepare($sql_month_orders);
                                    $stmt_month->bind_param("ii", $selected_month, $selected_year);
                                    $stmt_month->execute();
                                    $result_month = $stmt_month->get_result();
                                    $month_data = $result_month->fetch_assoc();

                                    $order_count = $month_data['month_order_count'] ?? 0;
                                    $total_sales = $month_data['total_sales'] ?? 0;

                                    $monthName = date('F', mktime(0, 0, 0, $selected_month, 1));
                                    echo "<h6 class='mb-3'>ข้อมูลสำหรับ <span class='text-primary'>$monthName $selected_year</span></h6>";
                                    echo "<p>จำนวนคำสั่งซื้อที่สำเร็จ: <span class='badge bg-secondary fs-6'>" . number_format($order_count) . "</span> รายการ</p>";
                                    echo "<p>ยอดขายรวม: <span class='badge bg-success fs-6'>" . number_format($total_sales, 2) . "</span> บาท</p>";
                                    ?>
                                </div>
                                <div class="mt-3">
                                    <a href="sales_report.php?month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>" class="btn btn-info w-100">ดูรายงานยอดขาย</a>
                                </div>
                                <p class='text-center'>(กรุณากดเลือกเดือนและกดกรองข้อมูลก่อนดูรายงานยอดขาย)</p>
                            </div>
                        </div>
                        <style>
                            .form-label {
                                font-weight: bold;
                                margin-bottom: 0.25rem;
                            }

                            .form-select {
                                border: 1px solid #ced4da;
                                border-radius: 0.25rem;
                                padding: 0.375rem 0.75rem;
                            }

                            .btn-primary {
                                color: #fff;
                                background-color: #0d6efd;
                                border-color: #0d6efd;
                            }

                            .btn-primary:hover {
                                color: #fff;
                                background-color: #0b5ed7;
                                border-color: #0a58ca;
                            }

                            .text-primary {
                                color: #0d6efd !important;
                            }

                            .badge {
                                display: inline-block;
                                padding: 0.35em 0.65em;
                                font-size: 0.75em;
                                font-weight: 700;
                                line-height: 1;
                                color: #fff;
                                text-align: center;
                                white-space: nowrap;
                                vertical-align: baseline;
                                border-radius: 0.25rem;
                            }

                            .bg-secondary {
                                background-color: #6c757d !important;
                            }

                            .bg-success {
                                background-color: #198754 !important;
                            }

                            .w-100 {
                                width: 100% !important;
                            }

                            .btn-info {
                                color: #fff;
                                background-color: #17a2b8;
                                border-color: #17a2b8;
                            }

                            .btn-info:hover {
                                color: #fff;
                                background-color: #138496;
                                border-color: #117a8b;
                            }
                        </style>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">คำสั่งซื้อต่อปี</h5>
                                <form method="GET" class="mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <label for="year" class="form-label">ปี</label>
                                            <select class="form-select" id="year" name="year">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($year = 2023; $year <= $currentYear + 1; $year++) {
                                                    $selected = (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : ($year == $currentYear ? 'selected' : '');
                                                    echo "<option value=\"$year\" $selected>$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary w-100">กรองข้อมูล</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="mt-4">
                                    <?php
                                    $selected_year_year = $_GET['year'] ?? date('Y');

                                    $sql_year_orders = "SELECT COUNT(*) as year_order_count, SUM(od.price * od.amount) as total_sales
                        FROM orders o
                        JOIN orderdetail od ON o.order_id = od.order_id
                        WHERE YEAR(o.order_date) = ? AND o.status = 'success'";
                                    $stmt_year = $conn->prepare($sql_year_orders);
                                    $stmt_year->bind_param("i", $selected_year_year);
                                    $stmt_year->execute();
                                    $result_year = $stmt_year->get_result();
                                    $year_data = $result_year->fetch_assoc();

                                    $year_order_count = $year_data['year_order_count'] ?? 0;
                                    $total_year_sales = $year_data['total_sales'] ?? 0;


                                    echo "<h6 class='mb-3'>ข้อมูลสำหรับปี <span class='text-primary'>$selected_year_year</span></h6>";
                                    echo "<p>จำนวนคำสั่งซื้อที่สำเร็จ: <span class='badge bg-secondary fs-6'>" . number_format($year_order_count) . "</span> รายการ</p>";
                                    echo "<p>ยอดขายรวม: <span class='badge bg-success fs-6'>" . number_format($total_year_sales, 2) . "</span> บาท</p>";
                                    ?>
                                </div>
                                <div class="mt-3">
                                    <a href="yearly_sales_report.php?year=<?php echo $selected_year_year; ?>" class="btn btn-info w-100">ดูรายงานยอดขาย</a>
                                </div>
                                <p class='text-center'>(กรุณากดเลือกปีและกดกรองข้อมูลก่อนดูรายงานยอดขาย)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<script>
    function updateOrders() {
        var selectedDate = document.getElementById('selectDate').value;
        var orderCountText = "0 รายการ";

        // ใช้ AJAX เพื่อดึงข้อมูลคำสั่งซื้อจาก server
        if (selectedDate) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_order_count.php?date=' + selectedDate, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    orderCountText = selectedDate + " : " + xhr.responseText + " รายการ";
                } else {
                    orderCountText = "เกิดข้อผิดพลาดในการดึงข้อมูล";
                }
                document.getElementById('orderCount').innerText = orderCountText;
            };
            xhr.send();
        } else {
            // ถ้าไม่มีการเลือกวันที่ ให้แสดงยอดสั่งซื้อของวันนี้
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_order_count.php?date=today', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    orderCountText = xhr.responseText + " รายการ";
                } else {
                    orderCountText = "เกิดข้อผิดพลาดในการดึงข้อมูล";
                }
                document.getElementById('orderCount').innerText = orderCountText;
            };
            xhr.send();
        }
    }


    function getOrdersForMonth() {
        const month = document.getElementById('monthSelect').value;
        // Use AJAX or a form to fetch data for the selected month
        // Example: update the count dynamically based on the selected month
        document.getElementById('orderCount').textContent = `${month} รายการ`; // Placeholder example
    }
</script>
<style>
    body {
        font-family: 'Arial', sans-serif;
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
        padding: 20px;
    }

    .card {
        margin-bottom: 20px;
    }
</style>