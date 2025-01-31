<!DOCTYPE html>
<html lang="en">
<?php
session_start();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong - Order</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <!-- Order Section -->
    <div class="container">
        <div class="card shadow">
            <div class="row">
                <?php
                include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

                if (!isset($_SESSION['user_id'])) {
                    echo "<script>alert('กรุณาเข้าสู่ระบบก่อน'); window.location.href='login-form.php';</script>";
                    exit();
                }

                $member_id = $_SESSION['user_id'];

                // ดึงข้อมูลสมาชิก
                $sql_member = "SELECT username, surname, phone_number, address FROM member WHERE member_id = ?";
                $stmt_member = $conn->prepare($sql_member);
                if ($stmt_member === false) {
                    die("Error preparing statement for member data: " . $conn->error);
                }

                $stmt_member->bind_param("i", $member_id);
                $stmt_member->execute();
                $result_member = $stmt_member->get_result();

                if ($result_member->num_rows > 0) {
                    $member = $result_member->fetch_assoc();
                    $username = $member['username'];
                    $surname = $member['surname'];
                    $phone_number = $member['phone_number'];
                    $address = $member['address'];
                } else {
                    echo "ไม่พบข้อมูลสมาชิก";
                    exit();
                }

                // ตรวจสอบคำสั่งซื้อที่ยังไม่เสร็จ (pending)
                $sql_order = "SELECT order_id FROM orders WHERE member_id = ? AND status = 'wait'";
                $stmt_order = $conn->prepare($sql_order);
                $stmt_order->bind_param("i", $member_id);
                $stmt_order->execute();
                $result_order = $stmt_order->get_result();

                if ($result_order->num_rows > 0) {
                    $order = $result_order->fetch_assoc();
                    $order_id = $order['order_id'];
                } else {
                    echo "ไม่พบคำสั่งซื้อที่ยังไม่เสร็จ";
                    exit();
                }

                // ดึงรายการสินค้าในคำสั่งซื้อ
                $sql_products = "
                                SELECT 
                                    p.product_name, 
                                    p.price, 
                                    od.amount,
                                    u.unit_name
                                FROM 
                                    orderdetail od
                                JOIN 
                                    product p 
                                ON 
                                    od.product_id = p.product_id
                                JOIN
                                    units u ON p.unit_id = u.unit_id
                                WHERE 
                                    od.order_id = ? AND od.selected = 1";
                $stmt_products = $conn->prepare($sql_products);
                $stmt_products->bind_param("i", $order_id);
                $stmt_products->execute();
                $result_products = $stmt_products->get_result();

                $products = [];
                $total_price = 0;

                while ($row = $result_products->fetch_assoc()) {
                    $products[] = $row;
                    $total_price += $row['price'] * $row['amount'];
                }
                ?>

                <div class="col-md-6" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                    <h4 class="mb-4" style="color: #343a40; border-bottom: 2px solid #dee2e6; padding-bottom: 10px;">ข้อมูลลูกค้า</h4>
                    <div class="mb-3">
                        <p class="mb-1"><strong style="color:#495057;">ชื่อผู้รับสินค้า:</strong> <span style="font-weight:500;"><?= htmlspecialchars($username) . " " . htmlspecialchars($surname) ?></span></p>
                        <p class="mb-1"><strong style="color:#495057;">เบอร์โทรผู้รับสินค้า:</strong> <span style="font-weight:500;"><?= htmlspecialchars($phone_number) ?></span></p>
                    </div>

                    <div class="mb-3">
                        <h5 class="mb-2" style="color: #343a40;">ที่อยู่จัดส่ง
                            <div class="float-right">
                                <button id="editAddressBtn" class="btn btn-outline-warning btn-sm">แก้ไข</button>
                                <button id="saveAddressBtn" class="btn btn-success btn-sm" style="display:none;">บันทึก</button>
                                <button id="cancelEditBtn" class="btn btn-danger btn-sm" style="display:none;">ยกเลิก</button>
                            </div>
                        </h5>
                        <div id="addressDisplay">
                            <p><span id="addressText"><?= htmlspecialchars($address) ?></span></p>
                        </div>
                        <div id="addressEdit" style="display:none;">
                            <textarea id="addressInput" class="form-control" rows="3"><?= htmlspecialchars($address) ?></textarea>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-2" style="color: #343a40; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">ข้อมูลสินค้า</h5>
                    <ul class="list-unstyled">
                        <?php foreach ($products as $index => $product) : ?>
                            <li class="mb-1" style="font-weight:500;">
                                <?= ($index + 1) . ". " . htmlspecialchars($product['product_name']) . " จำนวน " . $product['amount'] . " " . htmlspecialchars($product['unit_name']) . " - " . number_format($product['price'], 2) . " บาท" ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <p class="mt-3" style="font-size: 1.1rem;"><strong style="color: #495057;">ราคารวม:</strong> <span style="font-weight:bold;"><?= number_format($total_price, 2) ?> บาท</span></p>
                </div>
                <script>
                    document.getElementById("editAddressBtn").onclick = function() {
                        document.getElementById("addressDisplay").style.display = "none";
                        document.getElementById("addressEdit").style.display = "block";
                        document.getElementById("saveAddressBtn").style.display = "inline-block";
                        document.getElementById("cancelEditBtn").style.display = "inline-block";
                    };

                    document.getElementById("cancelEditBtn").onclick = function() {
                        document.getElementById("addressDisplay").style.display = "block";
                        document.getElementById("addressEdit").style.display = "none";
                        document.getElementById("saveAddressBtn").style.display = "none";
                        document.getElementById("cancelEditBtn").style.display = "none";
                    };

                    document.getElementById("saveAddressBtn").onclick = function() {
                        var newAddress = document.getElementById("addressInput").value;

                        if (newAddress.trim() === "") {
                            alert("กรุณากรอกที่อยู่");
                            return;
                        }
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "update_address.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState == 4 && xhr.status == 200) {
                                var response = xhr.responseText;
                                if (response === "success") {
                                    document.getElementById("addressText").innerText = newAddress;
                                    document.getElementById("addressDisplay").style.display = "block";
                                    document.getElementById("addressEdit").style.display = "none";
                                    document.getElementById("saveAddressBtn").style.display = "none";
                                    document.getElementById("cancelEditBtn").style.display = "none";
                                } else {
                                    alert(response);
                                }
                            }
                        };
                        xhr.send("address=" + encodeURIComponent(newAddress));
                    };
                </script>

                <div class="col-md-6">
                    <h4 class="mb-4" style="color: #343a40; border-bottom: 2px solid #dee2e6; padding-bottom: 10px;">เลือกวิธีการชำระเงิน</h4>

                    <div class="mb-3">
                        <div class="form-check py-2 px-3" style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            <input class="form-check-input" type="radio" name="payment" id="payment1" value="qr" style="transform: scale(2.5);margin-top: 0.25rem;">
                            <label class="form-check-label" for="payment1" style="margin-left: 10px; font-weight: 500;">ชำระผ่านการโอนเงิน</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check py-2 px-3" style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            <input class="form-check-input" type="radio" name="payment" id="payment2" value="cod" style="transform: scale(2.5);margin-top: 0.25rem;">
                            <label class="form-check-label" for="payment2" style="margin-left: 10px; font-weight: 500;">ชำระเงินปลายทาง</label>
                        </div>
                    </div>

                    <!-- Form for handling data submission -->
                    <form id="paymentForm" method="POST" action="QRcode.php" style="margin-top: 30px;">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                        <input type="hidden" name="payment_method" id="payment_method" value="">
                        <button type="submit" class="btn btn-primary btn-block mt-4" style="position: absolute;top: 70%;width: 93%;">ดำเนินการต่อ</button>
                    </form>

                    <!-- Back button -->
                    <button class="btn btn-secondary btn-block mt-3" onclick="window.history.back()">ย้อนกลับ</button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const paymentRadios = document.querySelectorAll('input[name="payment"]');
                        const paymentMethodInput = document.getElementById('payment_method');
                        const paymentForm = document.getElementById('paymentForm');

                        paymentRadios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                const selectedPayment = this.value;
                                paymentMethodInput.value = selectedPayment;

                                const formData = new FormData(paymentForm);
                                fetch('insert_payment.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            return response.text().then(text => {
                                                throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                                            });
                                        }
                                        return response.text();
                                    })
                                    .then(data => {
                                        console.log("Update Onchange", data);
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('เกิดข้อผิดพลาด โปรดลองใหม่อีกครั้ง');
                                    });
                            });
                        });

                        paymentForm.addEventListener('submit', function(event) {
                            event.preventDefault();

                            const orderId = document.querySelector('input[name="order_id"]').value;
                            const paymentMethod = document.getElementById('payment_method').value;

                            fetch(`check_payment_method.php?order_id=${orderId}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        const selectedPayment = data.payment_method; // ค่าที่ได้จากฐานข้อมูล (qr หรือ cod)
                                        console.log("Selected Payment:", selectedPayment);

                                        if (selectedPayment === 'qr') {
                                            paymentForm.submit();
                                        } else if (selectedPayment === 'cod') {
                                            window.location.assign(`ordertracking.php?order_id=${orderId}`);
                                        } else {
                                            alert("ไม่พบวิธีการชำระเงิน");
                                        }
                                    } else {
                                        alert("ไม่สามารถเช็คข้อมูลการชำระเงินได้");
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('เกิดข้อผิดพลาดในการเช็คข้อมูลวิธีการชำระเงิน');
                                });
                        });
                    });
                </script>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

</html>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editAddressBtn = document.getElementById('editAddressBtn');
        const saveAddressBtn = document.getElementById('saveAddressBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const addressDisplay = document.getElementById('addressDisplay');
        const addressEdit = document.getElementById('addressEdit');
        const addressText = document.getElementById('addressText');
        const addressInput = document.getElementById('addressInput');

        editAddressBtn.addEventListener('click', function() {
            addressDisplay.style.display = 'none';
            addressEdit.style.display = 'block';
            editAddressBtn.style.display = 'none';
            saveAddressBtn.style.display = 'inline-block';
            cancelEditBtn.style.display = 'inline-block';
        });

        saveAddressBtn.addEventListener('click', function() {
            addressText.textContent = addressInput.value;
            addressDisplay.style.display = 'block';
            addressEdit.style.display = 'none';
            editAddressBtn.style.display = 'inline-block';
            saveAddressBtn.style.display = 'none';
            cancelEditBtn.style.display = 'none';


        });

        cancelEditBtn.addEventListener('click', function() {
            addressDisplay.style.display = 'block';
            addressEdit.style.display = 'none';
            editAddressBtn.style.display = 'inline-block';
            saveAddressBtn.style.display = 'none';
            cancelEditBtn.style.display = 'none';
            addressInput.value = addressText.textContent
        });

    });

    function showLogoutPopup() {
        // Show the modal when the user clicks the logout link
        $('#logoutModal').modal('show');
    }
</script>
<style>
    .card {
        margin: 45px auto;
        padding: 20px;
        max-width: 900px;
        border-radius: 10px;
    }

    .btn-continue {
        background-color: #8B5E34;
        color: white;

    }

    .form-check-input {
        transform: scale(2);
        margin-top: 1.25rem;
    }

    .form-check {
        border-radius: 10px;
    }

    .btn-continue:hover {
        background-color: rgb(110, 71, 33);
        color: #fff;
        /* สีเมื่อ hover */
    }

    .btn {
        position: relative;
        top: 37%;
    }

    .navbar-nav .nav-link:hover {
        background-color: #A67B5B;
        color: #fff;
        border-radius: 5px;
    }
</style>