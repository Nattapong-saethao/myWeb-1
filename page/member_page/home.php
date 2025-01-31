<!DOCTYPE html>
<html lang="en">
<?php
session_start();
// db_connection.php ควรมีแค่การสร้าง $conn เท่านั้น
// การปิด connection ควรทำเมื่อจบ script
include '../php_script/db_connection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body style="
    background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.8), rgba(0, 0, 0, 0.3));
    display: flex;
    flex-direction: column;
    height: auto;
">
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>

    <div class="container-fluid">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1>ยินดีต้อนรับสู่ร้าน กาแฟเดอม้ง Coffee De Hmong</h1>
            <img src="coff1.avif" alt="Coffee Products">
            <button class="btn btn-primary" onclick="orderNow()">สั่งซื้อเลย</button>
        </div>

        <!-- About Us Section -->
        <div class="about-section">
            <h2>เกี่ยวกับเรา</h2>
            <p>กาแฟปลูกใต้ร่มเงา แบบเกษตรอินทรีย์ กาแฟรักษาป่า เพื่อคนอยู่คู่กับป่าอย่างยั่งยืน</p>
        </div>
        <!-- Product Section -->
        <div class="product-section">
            <h2>สินค้าแนะนำ</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="card product-card">
                        <img src="https://via.placeholder.com/200" alt="Lychee">
                        <h5>ลิ้นจี</h5>
                        <p>ลิ้นจี หวานฉ่ำ สดชื่น</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card product-card">
                        <img src=" https://via.placeholder.com/200" alt="Strawberry">
                        <h5>สตรอเบอรี่</h5>
                        <p>สตรอเบอรี่ เปรี้ยวอมหวาน</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card product-card">
                        <img src=" https://via.placeholder.com/200" alt="Peach">
                        <h5>ลูกไหน</h5>
                        <p>ลูกไหน เปรี้ยวหวาน หวาน ละมุน</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card product-card">
                        <img src=" https://via.placeholder.com/200" alt="Persimmon">
                        <h5>ลูกพลับ</h5>
                        <p>ลูกพลับ หวานกรอบ fffffอร่อย</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <footer>
            <p>ติดต่อเรา</p>
            <p>ที่อยู่: 265 ม.11 ต.งอบ อ.ทุ่งช้าง จ.น่าน, Nan, Thailand, Nan</p>
            <p>เบอร์โทร: 063 562 6696</p>
            <p>อีเมล: <a href="mailto:[coffeedehmong@gmail.com]">coffeedehmong@gmail.com</a></p>
            <p>ติดตามเราได้ที่: <a href="[https://www.facebook.com/coffeedehmong/?locale=th_TH]">Facebook</a> | <a href="[ลิงก์โซเชียลมีเดีย]">Instagram</a></p>
            <p>© 2024 ร้านกาแฟและผลไม้ Coffee De Hmong. All rights reserved.</p>
        </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
<script>
    function orderNow() {
        window.location.href = 'product_manu.php';
    }
</script>
<?php
// ปิดการเชื่อมต่อฐานข้อมูลเมื่อจบการทำงานของ script
if ($conn) {
    $conn->close();
}
?>
<style>
    body {
        font-family: 'Sarabun', sans-serif;
        color: #333;
        /* สีข้อความหลัก */
    }

    .hero-section {
        text-align: center;
        padding: 50px 20px;
        background-color: #f5f0e1;
        /* สีพื้นหลัง */
    }

    .hero-section h1 {
        color: #8B5E34;
        font-size: 2.5em;
        /* ขนาดตัวอักษร */
        margin-bottom: 20px;
    }

    .hero-section img {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 30px;
        box-shadow: 6px 3px 5px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }

    .hero-section button {
        background-color: #8B5E34;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        font-size: 1.1em;
        cursor: pointer;
    }

    .hero-section button:hover {
        background-color: #6d4323;
        /* สีเมื่อ hover */
        border-color: rgb(82, 40, 8);
    }

    .about-section {
        padding: 50px 20px;
        text-align: center;
        background-color: #fefae0;
        /* สีพื้นหลัง */
        color: #6d4323;
    }

    .about-section h2 {
        color: #8B5E34;
        margin-bottom: 20px;
        font-size: 2em;
        /* ขนาดตัวอักษร */
    }

    .about-section p {
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .about-section img {
        max-width: 300px;
        /* กำหนดความกว้างสูงสุด */
        height: auto;
        /* ให้ความสูงปรับตามสัดส่วน */
        border-radius: 15px;
        box-shadow: 3px 3px 5px rgba(0, 0, 0, 0.2);
        margin-top: 20px;
        /* เพิ่มระยะห่างด้านบน */
    }

    .product-section {
        padding: 50px 20px;
        background-color: #f5f0e1;
    }

    .product-section h2 {
        text-align: center;
        color: #8B5E34;
        margin-bottom: 30px;
        font-size: 2em;
        /* ขนาดตัวอักษร */
    }

    .product-card {
        background-color: #FED8B1;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 6px 4px 5px rgba(0, 0, 0, 0.2);
        padding: 10px;
    }

    .product-card img {
        width: 100%;
        height: 365px;
        object-fit: cover;
        border-radius: 10px 10px 0 0;
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
    }

    .product-card h5 {
        text-align: center;
        margin-top: 10px;
        color: #6d4323;
    }

    .product-card p {
        text-align: center;
        margin-bottom: 10px;
        color: #6d4323;

    }

    .botton-section {
        padding: 14px;
        color: #6F4E37;
    }

    .btn-primary {
        background-color: #8B5E34;
        border-color: #8B5E34;
    }

    .btn-primary:hover {
        background-color: #6d4323;
        /* สีเมื่อ hover */
        border-color: rgb(82, 40, 8);
    }

    footer {
        background-color: #33272a;
        /* สีพื้นหลังของ Footer */
        color: #fff;
        padding: 20px 0;
        text-align: center;
    }

    footer p {
        margin-bottom: 5px;
        /* ระยะห่างบรรทัด */
    }

    footer a {
        color: #fff;
        /* สีลิงก์ใน Footer */
        text-decoration: none;
        /* ไม่ให้มีเส้นใต้ */
    }

    footer a:hover {
        text-decoration: underline;
        /* เส้นใต้เมื่อ hover */
    }
</style>