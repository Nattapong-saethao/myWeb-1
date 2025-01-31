<?php
session_start();
include '../php_script/db_connection.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo "กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าในตะกร้า";
    exit;
}

// รับค่าจากแบบฟอร์ม
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$user_id = $_SESSION['user_id'];

// ตรวจสอบว่ามีคำสั่งซื้อที่สถานะเป็น 'cart' อยู่หรือไม่
$sql = "SELECT order_id FROM Orders WHERE member_id = ? AND status = 'cart'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ถ้ามี order ที่สถานะเป็น 'cart' อยู่ ให้ใช้ order_id เดิม
    $row = $result->fetch_assoc();
    $order_id = $row['order_id'];
} else {
    // ถ้าไม่มี order ที่สถานะเป็น 'cart' ให้สร้าง order ใหม่
    $order_date = date("Y-m-d H:i:s");
    $status = 'cart';
    $total_amount = 0;
    $price = 0;

    $sql = "INSERT INTO Orders (member_id, order_date, status, total_amount, price) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issii", $user_id, $order_date, $status, $total_amount, $price);
    $stmt->execute();
    $order_id = $stmt->insert_id; // รับค่า order_id ที่เพิ่งสร้างใหม่
}

// ตรวจสอบว่ามีสินค้าเดิมอยู่ในตะกร้าหรือไม่
$sql = "SELECT orderdetail_id, amount FROM Orderdetail WHERE order_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ถ้ามีสินค้าเดิมอยู่ในตะกร้า ให้เพิ่มจำนวนสินค้า
    $row = $result->fetch_assoc();
    $new_amount = $row['amount'] + $quantity;

    $sql = "UPDATE Orderdetail SET amount = ? WHERE orderdetail_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_amount, $row['orderdetail_id']);
    $stmt->execute();
} else {
    // ถ้ายังไม่มีสินค้าเดิม ให้เพิ่มรายการสินค้าใหม่ในตะกร้า
    $sql = "INSERT INTO Orderdetail (order_id, product_id, amount, price) VALUES (?, ?, ?, (SELECT price FROM Product WHERE product_id = ?))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $order_id, $product_id, $quantity, $product_id);
    $stmt->execute();
}

// อัปเดตราคาทั้งหมดในตาราง Orders
$sql = "UPDATE Orders 
        SET total_amount = (SELECT SUM(amount) FROM Orderdetail WHERE order_id = ?),
            price = (SELECT SUM(amount * price) FROM Orderdetail WHERE order_id = ?)
        WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $order_id, $order_id, $order_id);
$stmt->execute();

echo "เพิ่มสินค้าในตะกร้าเรียบร้อยแล้ว";
header("Location: cartproduct.php");
exit;
