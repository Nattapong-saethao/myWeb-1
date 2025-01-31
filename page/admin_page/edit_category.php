<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_POST['category_id']) ? "แก้ไขประเภทสินค้า" : "เพิ่มประเภทสินค้า"; ?></title>
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

    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;
    $category_name = "";
    $category_detail = ""; // เพิ่มตัวแปรสำหรับ category_detail

    // ถ้ามี category_id ให้ดึงข้อมูลมาแสดง
    if ($category_id) {
        try {
            $sql = "SELECT category_name, category_detail FROM productcategory WHERE category_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $category = $result->fetch_assoc();
                $category_name = $category['category_name'];
                $category_detail = $category['category_detail']; // ดึงข้อมูล category_detail
            } else {
                echo "<script>alert('ไม่พบประเภทสินค้าที่ต้องการแก้ไข')</script>";
                $category_id = null;
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            echo "<script>alert('Database Error: " . $e->getMessage() . "')</script>";
            $category_id = null;
        }
    }

    // ถ้ามีการ submit form
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $category_name = $_POST['category_name'];
        $category_detail = $_POST['category_detail']; // รับค่า category_detail จาก form

        if (empty($category_name)) {
            echo "<script>alert('กรุณากรอกชื่อประเภทสินค้า')</script>";
        } else {
            try {
                if ($category_id) {
                    // อัปเดตข้อมูล
                    $sql = "UPDATE productcategory SET category_name = ?, category_detail = ? WHERE category_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $category_name, $category_detail, $category_id); // แก้ query ให้ update ที่ productcategory
                    if ($stmt->execute()) {
                        echo "<script>alert('อัปเดตข้อมูลสำเร็จ')</script>";
                    } else {
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล')</script>";
                    }
                    $stmt->close();
                } else {
                    // เพิ่มข้อมูลใหม่
                    $sql = "INSERT INTO productcategory (category_name, category_detail) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $category_name, $category_detail); // แก้ query ให้ insert เข้า productcategory
                    if ($stmt->execute()) {
                        echo "<script>alert('เพิ่มข้อมูลสำเร็จ')</script>";
                        $category_name = ""; //Clear form
                        $category_detail = "";
                    } else {
                        echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มข้อมูล')</script>";
                    }
                    $stmt->close();
                }
            } catch (mysqli_sql_exception $e) {
                echo "<script>alert('Database Error: " . $e->getMessage() . "')</script>";
            }
        }
    }
    ?>
    <div class="main-content">
        <h2><?php echo isset($category_id) ? "แก้ไขประเภทสินค้า" : "เพิ่มประเภทสินค้า"; ?></h2>
        <form method="post" action="" class="mt-3">
            <div class="form-group">
                <label for="category_name">ชื่อประเภทสินค้า:</label>
                <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>">
            </div>
            <div class="form-group">
                <label for="category_detail">รายละเอียดประเภทสินค้า:</label>
                <input type="text" class="form-control" id="category_detail" name="category_detail" value="<?php echo htmlspecialchars($category_detail); ?>">
            </div>
            <input type="hidden" name="category_id" value="<?php echo isset($category_id) ? $category_id : ''; ?>">
            <button type="submit" name="submit" class="btn btn-primary"><?php echo isset($category_id) ? "แก้ไข" : "เพิ่ม"; ?></button>
            <a href="manage_category.php" class="btn btn-secondary">ย้อนกลับ</a>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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