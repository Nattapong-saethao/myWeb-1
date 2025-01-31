<!DOCTYPE html>
<html lang="th">
<?php
session_start();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <?php

    // ตรวจสอบว่า session มี user_id หรือไม่
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // กำหนดค่า user_id จาก session
    } else {
        echo "Error: User not logged in"; // ถ้าไม่มี session ของ user_id
        exit(); // หยุดการทำงาน
    }

    include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

    $product_id = $_GET['id']; // รับค่า product_id จาก URL
    $sql = "SELECT p.product_name, p.product_detail, p.price, p.amount, p.image, c.category_name 
        FROM Product p
        INNER JOIN Productcategory c ON p.category_id = c.category_id
        WHERE p.product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $product_name = $row['product_name'];
        $product_detail = $row['product_detail'];
        $price = $row['price'];
        $amount = $row['amount'];
        $image = $row['image'];
        $category_name = $row['category_name'];
    } else {
        echo "ไม่พบข้อมูลสินค้า";
    }
    $stmt->close();
    ?>
    <div class="container mt-5">
        <div class="row" style="height: 500px;">
            <div class="col-md-6">
                <a href="#" class="btn btn-secondary mb-3" style="margin-top: 12px;" onclick="goToDetail()">&larr; กลับ</a>
                <div class="card" style="height: 300px; background-image: url('/myWeb/page/admin_page/<?php echo $image; ?>'); background-size: cover; background-position: center;">
                </div>
            </div>
            <div class="col-md-6">
                <h1 style="line-height: 65px;"><?php echo $product_name; ?></h1>
                <p><strong>ประเภทสินค้า:</strong> <?php echo $category_name; ?></p>
                <p><strong>รายละเอียดของสินค้า:</strong></p>
                <div><?php echo nl2br($product_detail); ?></div>
                <div class="form-group" style="margin-top: 20%;">
                    <p><strong>ราคา:</strong> <?php echo $price; ?> บาท</p>
                    <p><strong>จำนวนสินค้าคงเหลือ:</strong> <?php echo $amount; ?>ถุง</p>
                    <label for="quantity">จำนวนที่ต้องการ:</label>
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                        <input type="number" class="form-control" id="quantity" value="1" min="1" max="<?php echo $amount; ?>" style="width: 100px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
                <script src="cartproduct.js"></script>
                <?php
                if (isset($_GET['id'])) {
                    $product_id = $_GET['id'];
                    // คำสั่ง SQL ที่ถูกต้อง
                    $sql = "SELECT * FROM product WHERE product_id = ?";
                    // ตรวจสอบว่าคำสั่ง SQL ถูกต้องหรือไม่
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $product = $result->fetch_assoc();
                        } else {
                            echo "ไม่พบข้อมูลผลิตภัณฑ์";
                            exit;
                        }
                    } else {
                        echo "ข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
                        exit;
                    }
                } else {
                    echo "ไม่มี ID ของผลิตภัณฑ์";
                    exit;
                }
                ?>

                <button class="btn btn-primary" onclick="addToCart(<?php echo $product['product_id']; ?> , document.getElementById('quantity').value); return false;">เพิ่มลงตะกร้า</button>

                <button class="btn btn-continue" type="submit" onclick="window.location.href='cartproduct.php'" style="background-color: #4CAF50;color: white;">ตะกร้าของฉัน</button>

                <script>
                    $(document).ready(function() {
                        console.log("jQuery Loaded:", typeof $ !== 'undefined'); // ตรวจสอบการโหลดของ jQuery
                    });

                    function addToCart(productId, quantity) {
                        console.log("Product ID:", productId);
                        console.log("Quantity:", quantity);

                        // ตรวจสอบว่ามีการประกาศตัวแปร amount หรือไม่
                        <?php if (isset($amount)) { ?>
                            console.log("Amount:", <?php echo $amount; ?>);
                        <?php } else { ?>
                            console.log("Amount: ไม่ได้กำหนด");
                        <?php } ?>

                        // ตรวจสอบว่ามีการตั้งค่าผู้ใช้หรือไม่
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            console.log("User:", <?php echo $_SESSION['user_id']; ?>);
                        <?php } else { ?>
                            console.log("User: ไม่ได้ล็อกอิน");
                            alert('กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าในตะกร้า');
                            return;
                        <?php } ?>

                        $.ajax({
                            url: 'order_step.php',
                            type: 'POST',
                            data: {
                                product_id: productId,
                                quantity: quantity
                            },
                            success: function(response) {
                                console.log("Response:", response); // ดูค่าตอบกลับจากเซิร์ฟเวอร์
                                alert('เพิ่มสินค้าในตะกร้าสำเร็จ!');
                            },
                            error: function(xhr, status, error) {
                                console.log("Error:", error);
                                alert('เกิดข้อผิดพลาดในการเพิ่มสินค้า');
                            }
                        });
                    }



                    function increaseQuantity() {
                        let quantityInput = document.getElementById('quantity');
                        let maxQuantity = <?php echo $amount; ?>; // รับค่าจำนวนสินค้าในสต็อกจาก PHP

                        // ตรวจสอบว่า quantity ไม่เกิน maxQuantity
                        if (parseInt(quantityInput.value) < maxQuantity) {
                            quantityInput.value = parseInt(quantityInput.value) + 1;
                        } else {
                            alert("จำนวนสินค้าสูงสุดที่สามารถสั่งได้คือ " + maxQuantity + " ชิ้น");
                        }
                    }


                    function decreaseQuantity() {
                        let quantityInput = document.getElementById('quantity');
                        if (quantityInput.value > 1) { // ป้องกันไม่ให้ค่าต่ำกว่า 1
                            quantityInput.value = parseInt(quantityInput.value) - 1;
                        }
                    }

                    function goToDetail() {
                        window.location.href = 'product_manu.php';
                    }
                </script>
</body>

</html>
<style>
    .content {
        margin-top: 50px;
    }

    .card {
        padding: 200px;
        border-radius: 10px;
    }

    .btn-custom {
        background-color: #8b5e3c;
        color: #fff;
    }

    .col-md-6 {
        background-color: #FFEDD8;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        height: 100%;
    }
</style>