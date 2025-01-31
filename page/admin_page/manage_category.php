<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทสินค้า</title>
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

    // ดึงข้อมูลจากตาราง category
    try {
        $sql = "SELECT category_id, category_name, category_detail FROM productcategory ORDER BY category_id"; // แก้ query
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }
    ?>
    <div class="main-content">
        <h2>จัดการประเภทสินค้า</h2>
        <button class="btn btn-success mb-3" onclick="goToDetail()">+ เพิ่มประเภทสินค้า</button>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ลำดับ</th>
                    <th>รหัสประเภทสินค้า</th>
                    <th>ชื่อประเภทสินค้า</th>
                    <th>รายละเอียด</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories): ?>
                    <?php foreach ($categories as $index => $category): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_detail']); ?></td>
                            <td>
                                <form method="POST" action="edit_category.php" style="display:inline;">
                                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                    <button class="btn btn-sm btn-warning">แก้ไข</button>
                                </form>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['category_id']; ?>)">ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function goToDetail() {
            window.location.href = 'edit_category.php'; // แก้ไปยังหน้าเพิ่มประเภทสินค้า
        }

        function deleteCategory(categoryId) {
            if (confirm("คุณต้องการลบประเภทสินค้านี้หรือไม่?")) {
                window.location.href = 'delete_category.php?category_id=' + categoryId;
            }
        }
    </script>

</body>

</html>
<?php $conn->close(); ?>
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