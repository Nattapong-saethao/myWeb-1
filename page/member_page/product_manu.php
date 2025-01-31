<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<?php
session_start();
include '../php_script/db_connection.php';

// ตรวจสอบว่ามีการส่ง category_id มาทาง GET หรือไม่
if (isset($_GET['category_id'])) {
    $selected_category_id = $_GET['category_id'];
    // ถ้ามี category_id ให้ดึงสินค้าตาม category_id
    $sql = "SELECT p.product_id, p.product_name, p.price, p.amount, p.image, u.unit_name
            FROM Product p
            JOIN units u ON p.unit_id = u.unit_id
            WHERE p.category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // ถ้าไม่มี category_id ให้ดึงสินค้าทั้งหมด
    $sql = "SELECT p.product_id, p.product_name, p.price, p.amount, p.image, u.unit_name
            FROM Product p
            JOIN units u ON p.unit_id = u.unit_id";
    $result = $conn->query($sql);
}
?>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <!-- Product Section -->
    <div class="container mt-5">
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    echo '
                    <div class="col-md-3 mb-4">
                        <div class="product-card" onclick="goToDetail(' . $product["product_id"] . ')">
                            <div class="product-image mb-3" style="height: 150px; background-color: #f8f8f8; background-image: url(\'/myWeb/page/admin_page/' . $product["image"] . '\'); background-size: cover; background-position: center;"></div>
                            <h5>' . $product["product_name"] . '</h5>
                            <p>ราคา: ' . $product["price"] . ' บาท</p>
                             <p>เหลือ: ' . $product["amount"] . ' ' . $product["unit_name"] . '</p>
                        </div>
                    </div>
                ';
                }
            } else {
                echo '<p class="text-center">ไม่มีสินค้าในขณะนี้</p>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function goToDetail(productId) {
            window.location.href = 'productdetail.php?id=' + productId;
        }
    </script>
</body>

</html>
<style>
    .product-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background-color: white;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        background-color: #f1f1f1;
    }
</style>