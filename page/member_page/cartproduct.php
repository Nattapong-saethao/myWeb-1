<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="cartproduct.js"></script>

</head>

<body>
    <!-- Navbar -->

    <?php
    session_start();
    include 'navbar.php';
    ?>
    <?php
    include '../php_script/db_connection.php';

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $member_id = $_SESSION['user_id'];
    // ดึงข้อมูลตะกร้าจากฐานข้อมูล
    $sql = "SELECT od.*, p.product_name, p.price, p.image 
        FROM orderdetail od 
        JOIN product p ON od.product_id = p.product_id
        JOIN orders o ON od.order_id = o.order_id
        WHERE o.member_id = ? AND o.status = 'wait'"; // คำสั่ง SQL ที่ดึงข้อมูล

    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Error in SQL prepare: ' . $conn->error);  // แสดงข้อผิดพลาดจากการเตรียมคำสั่ง SQL
    }

    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Table Display
    ?>

    <!-- Table Container -->
    <div class="container table-container" style="background-color: #f5f5f5;">
        <h2>ตะกร้าของฉัน</h2>
        <table class="table table-bordered cart-table center">
            <thead>
                <tr>
                    <th>เลือก</th>
                    <th>รูป</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวน</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalPrice = 0; // ตัวแปรรวมราคาทั้งหมด
                while ($row = $result->fetch_assoc()) {
                    $totalPrice += $row['price'] * $row['amount']; // คำนวณราคาทั้งหมด
                ?>
                    <tr data-price="<?php echo $row['price']; ?>" data-amount="<?php echo $row['amount']; ?>">
                        <td><input type="checkbox" class="item-select" style="transform: scale(2);" data-orderdetail-id="<?php echo $row['orderdetail_id']; ?>"></td>
                        <td><img src="/myWeb/page/admin_page/<?php echo $row['image']; ?>" alt="Product Image" style="width: 50px;"></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> บาท</td>
                        <td>
                            <button class="btn btn-sm btn-light btn-minus" data-orderdetail-id="<?php echo $row['orderdetail_id']; ?>">-</button>
                            <span class="quantity"><?php echo $row['amount']; ?></span>
                            <button class="btn btn-sm btn-light btn-plus" data-orderdetail-id="<?php echo $row['orderdetail_id']; ?>">+</button>
                        </td>
                        <td>
                            <form method="POST" action="delete_item.php" style="display:inline;">
                                <input type="hidden" name="orderdetail_id" value="<?php echo $row['orderdetail_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">&#128465;</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="total-price">
            ราคารวม <span id="total-price"><?php echo number_format($totalPrice, 2); ?></span> บาท
        </div>
        <script>
            // คำนวณราคารวมใหม่และอัปเดต DOM
            function updateTotalPrice() {
                let totalPrice = 0;
                document.querySelectorAll('.item-select:checked').forEach(function(checkbox) {
                    const row = checkbox.closest('tr');
                    const pricePerItem = parseFloat(row.dataset.price);
                    const quantityElement = row.querySelector('.quantity');
                    const quantity = parseInt(quantityElement.textContent);
                    const itemTotalPrice = pricePerItem * quantity;

                    totalPrice += itemTotalPrice;
                    row.dataset.itemTotalPrice = itemTotalPrice; // เก็บราคารวมต่อรายการใน data attribute
                });
                document.getElementById('total-price').textContent = totalPrice.toFixed(2);
            }

            // อัปเดตค่า select และ ราคารวมต่อรายการในฐานข้อมูล
            function updateSelectItem(orderDetailId, isSelected) {
                const row = document.querySelector(`.item-select[data-orderdetail-id="${orderDetailId}"]`).closest('tr');
                const quantity = parseInt(row.querySelector('.quantity').textContent);
                const pricePerItem = parseFloat(row.dataset.price);
                const itemTotalPrice = quantity * pricePerItem;

                fetch('update_quantity.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderdetail_id: orderDetailId,
                            select: isSelected ? 1 : 0,
                            amount: quantity,
                            price: itemTotalPrice, // ส่งราคารวมต่อรายการไปด้วย
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            document.querySelectorAll('.btn-plus, .btn-minus').forEach(button => {
                button.addEventListener('click', function() {
                    const orderDetailId = this.dataset.orderdetailId;
                    const isIncrement = this.classList.contains('btn-plus');
                    const quantityElement = this.closest('td').querySelector('.quantity');
                    let currentQuantity = parseInt(quantityElement.textContent);
                    const row = this.closest('tr');
                    const initialPrice = parseFloat(row.dataset.price);
                    const isSelected = document.querySelector(`.item-select[data-orderdetail-id="${orderDetailId}"]`).checked;

                    const newQuantity = isIncrement ? currentQuantity + 1 : currentQuantity - 1;

                    if (newQuantity < 1) return;

                    const itemTotalPrice = newQuantity * initialPrice;

                    fetch('update_quantity.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                orderdetail_id: orderDetailId,
                                amount: newQuantity,
                                select: isSelected ? 1 : 0,
                                price: itemTotalPrice, // ส่งราคารวมต่อรายการไปด้วย
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                quantityElement.textContent = newQuantity;
                                row.dataset.amount = newQuantity;
                                row.dataset.itemTotalPrice = itemTotalPrice;
                                updateTotalPrice();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });

            document.querySelectorAll('.item-select').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const orderDetailId = this.dataset.orderdetailId;
                    updateSelectItem(orderDetailId, this.checked);
                    updateTotalPrice();
                });
            });

            document.getElementById('checkout-btn').addEventListener('click', function() {
                const selectedItems = [];
                document.querySelectorAll('.item-select:checked').forEach(checkbox => {
                    selectedItems.push(checkbox.dataset.orderdetailId);
                });

                if (selectedItems.length > 0) {
                    fetch('checkout.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                orderdetails: selectedItems
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('สั่งซื้อสำเร็จ');
                                location.reload(); // รีเฟรชหน้า
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    alert('กรุณาเลือกสินค้าที่จะสั่งซื้อ');
                }
            });

            // เรียกใช้การคำนวณราคารวมในตอนเริ่มต้น
            updateTotalPrice();
        </script>


        <button class="btn btn-next btn-block mt-3" onclick="goToDetail()">ถัดไป</button>
        <button class="btn btn-secondary mt-4 w-100" onclick="window.history.back()" style="max-width:300px;">ย้อนกลับ</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<script>
    function showLogoutPopup() {
        // Show the modal when the user clicks the logout link
        $('#logoutModal').modal('show');
    }
</script>
<style>
    .table-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        /* ให้ครอบคลุมความสูงทั้งหน้าจอ */
        text-align: center;
        margin-top: 75px;
    }

    .cart-table {
        width: 80%;
        /* กำหนดความกว้างของตาราง */
        max-width: 900px;
    }

    .total-price {
        margin-top: 20px;
        font-size: 18px;
        font-weight: bold;
    }

    .btn-next {
        background-color: #8b5e3c;
        color: #fff;
    }

    .btn-next {
        width: 80%;
        /* ปรับให้ปุ่มกว้างขึ้น */
        max-width: 300px;
    }


    .btn-light {
        background-color: #d4d0cd;
    }

    .btn-next:hover {
        background-color: #6d4323;
        /* สีเมื่อ hover */
        color: #fff;
    }

    .navbar-nav .nav-link:hover {
        background-color: #A67B5B;
        color: #fff;
        border-radius: 5px;
    }
</style>