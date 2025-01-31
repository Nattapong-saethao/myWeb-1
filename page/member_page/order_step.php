<?php
session_start(); // เริ่มต้น session
include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // กำหนดค่า $user_id จาก session
} else {
    echo "<script>
    alert('กรุณาเข้าสู่ระบบก่อน');
    window.location.href = 'login-form.php';
</script>";
    exit(); // หยุดการทำงานหากไม่ได้เข้าสู่ระบบ
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($product_id <= 0 || $quantity <= 0) {
    echo "Invalid product ID or quantity";
    exit();
}

// ตรวจสอบว่ามี order ที่ยังไม่เสร็จ (pending) สำหรับผู้ใช้อยู่หรือไม่
$sql_check_order = "SELECT order_id FROM orders WHERE member_id = ? AND status = 'wait'";
$stmt_check_order = $conn->prepare($sql_check_order);
$stmt_check_order->bind_param("i", $user_id);
$stmt_check_order->execute();
$result = $stmt_check_order->get_result();

if ($result->num_rows > 0) {
    // ถ้ามี order อยู่แล้ว ให้ใช้ order_id นั้น
    $order = $result->fetch_assoc();
    $order_id = $order['order_id'];
} else {
    // ถ้าไม่มี order ให้สร้าง order ใหม่
    $sql_create_order = "INSERT INTO orders (member_id, order_date, status, total_amount) VALUES (?, NOW(), 'wait', 0)";
    $stmt_create_order = $conn->prepare($sql_create_order);
    $stmt_create_order->bind_param("i", $user_id);

    if ($stmt_create_order->execute()) {
        $order_id = $stmt_create_order->insert_id; // ดึงค่า order_id ของ order ที่เพิ่งสร้าง
    } else {
        echo "Error creating new order: " . $stmt_create_order->error;
        exit();
    }
}

// ตรวจสอบว่ามีสินค้าตัวเดิมอยู่ใน orderdetail หรือไม่
$sql_check_detail = "SELECT amount FROM orderdetail WHERE order_id = ? AND product_id = ?";
$stmt_check_detail = $conn->prepare($sql_check_detail);
$stmt_check_detail->bind_param("ii", $order_id, $product_id);
$stmt_check_detail->execute();
$result_check_detail = $stmt_check_detail->get_result();

if ($result_check_detail->num_rows > 0) {
    // ถ้ามีสินค้าเดิมอยู่ ให้เพิ่มจำนวน
    $existing_detail = $result_check_detail->fetch_assoc();
    $new_amount = $existing_detail['amount'] + $quantity;

    $sql_update_detail = "UPDATE orderdetail SET amount = ? WHERE order_id = ? AND product_id = ?";
    $stmt_update_detail = $conn->prepare($sql_update_detail);
    $stmt_update_detail->bind_param("iii", $new_amount, $order_id, $product_id);

    if ($stmt_update_detail->execute()) {
        echo "Updated product quantity successfully";
    } else {
        echo "Error updating product quantity: " . $stmt_update_detail->error;
    }
} else {
    // ถ้าไม่มีสินค้าเดิม ให้เพิ่มรายการใหม่
    $sql_detail = "INSERT INTO orderdetail (order_id, product_id, amount) VALUES (?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);

    if ($stmt_detail) {
        $stmt_detail->bind_param("iii", $order_id, $product_id, $quantity);
        if ($stmt_detail->execute()) {
            echo "Added product successfully";
        } else {
            echo "Error executing orderdetail query: " . $stmt_detail->error;
        }
    } else {
        echo "Error preparing orderdetail query: " . $conn->error;
    }
}
