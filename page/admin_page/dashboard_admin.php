<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ร้านกาแฟและผลไม้</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="dashboard-container">
        <?php
        session_start();
        include('sidebar.php');
        include '../php_script/db_connection.php';

        // ดึงข้อมูลจากฐานข้อมูล
        try {
            // 1. ออเดอร์วันนี้
            $today = date("Y-m-d");
            $sql = "SELECT COUNT(*) FROM orders WHERE DATE(order_date) = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $stmt->bind_result($orderTodayCount);
            $stmt->fetch();
            $stmt->close();

            // 2. ยอดขายทั้งหมด (เฉพาะ orders ที่มี status เป็น 'success')
            $sql = "SELECT SUM(od.price * od.amount) 
            FROM orderdetail od 
            JOIN orders o ON od.order_id = o.order_id
            WHERE o.status = 'success'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($totalSales);
            $stmt->fetch();
            $totalSales = $totalSales ?: 0;
            $stmt->close();
            // 3. จำนวนพนักงาน
            $sql = "SELECT COUNT(*) FROM _user WHERE role = 2";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($employeeCount);
            $stmt->fetch();
            $stmt->close();

            // 4. จำนวนสมาชิก
            $sql = "SELECT COUNT(*) FROM member";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($memberCount);
            $stmt->fetch();
            $stmt->close();

            // 5. สินค้าที่เหลือ (น้อยกว่าหรือเท่ากับ 10)
            $sql = "SELECT p.product_id, p.category_id, p.product_name, p.amount, u.unit_name 
            FROM product p
            INNER JOIN units u ON p.unit_id = u.unit_id
            WHERE p.amount <= 10";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $lowStockProducts = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // 6. ออเดอร์ล่าสุด (ไม่เกิน 2 วัน และ selected = 1)
            $twoDaysAgo = date("Y-m-d", strtotime("-2 days"));
            $sql = "
    SELECT
        o.order_id,
        m.username,
        m.surname,
        GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products,
        SUM(od.price ) AS total_price,
        o.order_date
    FROM orders o
    INNER JOIN member m ON o.member_id = m.member_id
    INNER JOIN orderdetail od ON o.order_id = od.order_id
    INNER JOIN product p ON od.product_id = p.product_id
    WHERE o.order_date >= ? AND od.selected = 1
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT 5;
    ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $twoDaysAgo);
            $stmt->execute();
            $result = $stmt->get_result();
            $recentOrders = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            echo "Database Error: " . $e->getMessage();
            die();
        }

        include('sidebar.php');
        ?>

        <main class="main-content">
            <header class="main-header">
                <h1>Dashboard</h1>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>
                        <?php
                        if (isset($_SESSION['full_name'])) {
                            echo $_SESSION['full_name'];
                        } else {
                            echo "Admin"; // Default text
                        }
                        ?>
                    </span>
                </div>
            </header>

            <div class="content-area">
                <div class="overview-cards">
                    <div class="card">
                        <i class="fas fa-chart-bar card-icon"></i>
                        <div class="card-content">
                            <h3>ออเดอร์วันนี้</h3>
                            <p><?php echo $orderTodayCount; ?> รายการ</p>
                        </div>
                    </div>
                    <div class="card">
                        <i class="fas fa-money-bill-wave card-icon"></i>
                        <div class="card-content">
                            <h3>ยอดขายทั้งหมด</h3>
                            <p><?php echo number_format($totalSales, 2); ?> บาท</p>
                        </div>
                    </div>
                    <div class="card">
                        <i class="fas fa-shopping-cart card-icon"></i>
                        <div class="card-content">
                            <h3>จำนวนพนักงาน</h3>
                            <p><?php echo $employeeCount; ?> คน</p>
                        </div>
                    </div>
                    <div class="card">
                        <i class="fas fa-users card-icon"></i>
                        <div class="card-content">
                            <h3>จำนวนสมาชิก</h3>
                            <p><?php echo $memberCount; ?> คน</p>
                        </div>
                    </div>

                </div>

                <div class="charts-and-tables" style="margin-top: 10px;">
                    <div class="chart-container">
                        <h2>สินค้าที่เหลือน้อย</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ประเภทสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>จำนวนที่เหลือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php if ($product['category_id'] == 1) {
                                                echo ('ผลิตภัณฑ์กาแฟ');
                                            } else {
                                                echo ('ผลไม้');
                                            }
                                            ?></td>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['amount']) . ' ' . htmlspecialchars($product['unit_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="table-container">
                    <h2>ออเดอร์ล่าสุด</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ลูกค้า</th>
                                <th>รายการ</th>
                                <th>ยอดรวม</th>
                                <th>วันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $index => $order): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($order['username'] . ' ' . $order['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($order['products']); ?></td>
                                    <td><?php echo number_format($order['total_price'], 2); ?> บาท</td>
                                    <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>

</html>

<style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
        color: #333;
    }

    .dashboard-container {
        display: flex;
    }

    /* Main Content */
    .main-content {
        flex: 1;
        padding: 20px;
        margin-left: 250px;
    }

    .main-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .user-profile {
        display: flex;
        align-items: center;
    }

    .user-profile i {
        margin-right: 5px;
        font-size: 1.5em;
    }

    .content-area {
        margin-top: 20px;
    }

    /* Overview Cards */
    .overview-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .card-icon {
        font-size: 2em;
        margin-bottom: 10px;
        color: #007BFF;
    }

    .card-content h3 {
        margin: 0;
        font-size: 1.2em;
        margin-bottom: 5px;
    }

    .card-content p {
        margin: 0;
        font-size: 1.5em;
        font-weight: bold;
    }

    /* Charts and Tables */
    .charts-and-tables {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
    }

    .chart-container,
    .table-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-container h2,
    .table-container h2 {
        margin-top: 0;
        margin-bottom: 15px;
    }

    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: static;
        }

        .main-content {
            margin-left: 0;
            padding: 10px;
        }

        .charts-and-tables {
            grid-template-columns: 1fr;
        }
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

    @media (max-width: 768px) {
        .sidebar {
            position: absolute;
            height: auto;
            width: 100%;
        }

        .content {
            margin-left: 0;
            width: 100%;
        }
    }
</style>