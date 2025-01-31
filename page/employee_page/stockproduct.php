<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
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

    // ดึงข้อมูลจากตาราง product พร้อมข้อมูลการปรับปรุงล่าสุด
    try {
        $sql = "SELECT p.product_id, p.product_name, p.category_id, p.amount, p.image,
                       MAX(sa.adjustment_date) as last_adjustment_date,
                       MAX(u.username) as last_adjusted_by
                FROM product p
                LEFT JOIN stockadjustment sa ON p.product_id = sa.product_id
                LEFT JOIN _user u ON sa.user_id = u.user_id
                GROUP BY p.product_id
                ORDER BY p.product_id";


        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }
    ?>
    <div class="main-content">
        <h2>จัดการสินค้า</h2>
        <button class="btn btn-success mb-3" onclick="goToDetail()">+ เพิ่มสินค้า</button>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อ/Name</th>
                    <th>ประเภท/Categories</th>
                    <th>จำนวน/Quantity</th>
                    <th>วันที่ปรับปรุง/Update date</th>
                    <th>ผู้ปรับปรุงล่าสุด</th>
                    <th>รูปภาพ/Image</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products): ?>
                    <?php foreach ($products as $index => $product): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php if ($product['category_id'] == 1) {
                                    echo ('ผลิตภัณฑ์กาแฟ');
                                } else {
                                    echo ('ผลไม้');
                                }
                                ?></td>
                            <td><?php echo htmlspecialchars($product['amount']); ?></td>
                            <td><?php echo !empty($product['last_adjustment_date']) ? date('d/m/Y', strtotime($product['last_adjustment_date'])) : 'N/A'; ?></td>
                            <td><?php echo !empty($product['last_adjusted_by']) ? htmlspecialchars($product['last_adjusted_by']) : 'N/A'; ?></td>
                            <td>
                                <?php if ($product['image']): ?>
                                    <img src="../admin_page/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" class="img-fluid" style="max-width: 100px; max-height: 100px;">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="edit_item.php" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button class="btn btn-sm btn-warning">แก้ไข</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<script>
    function goToDetail() {
        window.location.href = 'addproduct-form.php';
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

    .main-content {
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