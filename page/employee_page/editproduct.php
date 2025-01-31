<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="sidebar">
        <div class="text-center mb-4">
            <img src="https://via.placeholder.com/100" class="rounded-circle" alt="Admin">
            <h4>admin</h4>
        </div>
        <a href="stockproduct.php">จัดการสต็อกสินค้า</a>
        <a href="order.php">รายการคำสั่งซื้อ</a>
        <a href="employemanage.php">จัดการข้อมูลผู้ใช้</a>
    </div>
    <div class="content">
        <h2>เพิ่มสินค้า</h2>
        <form>
            <div class="form-group">
                <label>ประเภทสินค้า</label><br>
                <input type="checkbox" id="coffee" name="productType" value="coffee">
                <label for="coffee">ผลิตภัณฑ์กาแฟ</label>
                <input type="checkbox" id="fruit" name="productType" value="fruit">
                <label for="fruit">ผลไม้</label>
            </div>
            <div class="form-group">
                <label for="productName">ชื่อสินค้า</label>
                <input type="text" class="form-control" id="productName" name="productName" style="max-width: 350px;">
            </div>
            <div class="form-group">
                <label for="productDetails">รายละเอียดสินค้า</label>
                <textarea class="form-control" id="productDetails" name="productDetails" style="max-width: 500px;"></textarea>
            </div>
            <div class="form-group">
                <label for="quantity">จำนวน</label>
                <input type="number" class="form-control" id="quantity" name="quantity" style="max-width: 350px;">
            </div>
            <div class="form-group">
                <label for="price">ราคา</label>
                <input type="text" class="form-control" id="price" name="price" style="max-width: 350px;">
            </div>
            <div class="form-group">
                <label for="imagePreview">รูปภาพ</label>
                <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; height: auto;">
            </div>

            <button type="submit" class="btn btn-success">บันทึก</button>
        </form>
    </div>
</body>

</html>
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