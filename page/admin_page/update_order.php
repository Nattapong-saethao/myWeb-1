<?php
// 1. เชื่อมต่อฐานข้อมูล (คุณต้องแก้ไขข้อมูลเหล่านี้ให้ถูกต้อง)
session_start();
include '../php_script/db_connection.php';

// 2. รับค่า order_id จาก form
if (isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    // $amount = $_POST['amount']; // ไม่จำเป็นต้องรับ amount มาตรงนี้

    // 3. คำสั่ง SQL ในการอัปเดต status
    if ($status == 'pending') {
        $sql = "UPDATE orders SET status = 'pickup' WHERE order_id = ?";
    } elseif ($status == 'pickup') {
        $sql = "UPDATE orders SET status = 'shipped' WHERE order_id = ?";
    } elseif ($status == 'shipped') {
        $sql = "UPDATE orders SET status = 'success' WHERE order_id = ?";
    }

    // 4. Prepare statement เพื่อป้องกัน SQL injection สำหรับ update status
    $stmt_order = $conn->prepare($sql);
    $stmt_order->bind_param("i", $orderId);

    // 5. Execute statement สำหรับ update status และตรวจสอบว่าสำเร็จหรือไม่
    if ($stmt_order->execute()) {

        // ลดจำนวน stock สินค้าเมื่อ status เป็น pending เท่านั้น
        if ($status == 'pending') {
            // ดึงรายการสินค้าในคำสั่งซื้อ เพื่อนำมาลดจำนวนสินค้า
            $sql_order_detail = "SELECT od.amount, p.product_id 
                         FROM orderdetail od
                         JOIN product p ON od.product_id = p.product_id
                         WHERE od.order_id = ?";
            $stmt_order_detail = $conn->prepare($sql_order_detail);
            $stmt_order_detail->bind_param("i", $orderId);
            $stmt_order_detail->execute();
            $result_order_detail = $stmt_order_detail->get_result();

            // วน loop เพื่อ update stock สินค้า
            while ($order_detail = $result_order_detail->fetch_assoc()) {
                $product_id = $order_detail['product_id'];
                $order_amount = $order_detail['amount'];

                //ดึง stock ปัจจุบันของสินค้า
                $sql_product = "SELECT amount FROM product WHERE product_id = ?";
                $stmt_product = $conn->prepare($sql_product);
                $stmt_product->bind_param("i", $product_id);
                $stmt_product->execute();
                $result_product = $stmt_product->get_result();
                $product = $result_product->fetch_assoc();
                $current_amount = $product['amount'];


                // คำนวน stock คงเหลือ และ update ลง database
                $new_amount = $current_amount - $order_amount;
                $sql_update_product = "UPDATE product SET amount = ? WHERE product_id = ?";
                $stmt_update_product = $conn->prepare($sql_update_product);
                $stmt_update_product->bind_param("ii", $new_amount, $product_id);
                $stmt_update_product->execute();
                $stmt_update_product->close();

                $stmt_product->close();
            }

            $stmt_order_detail->close();
        }

        echo "Order status updated and stock reduced successfully!";
        header("Location: pending.php");
    } else {
        echo "Error updating order status: " . $conn->error;
    }

    // 6. ปิด statement และ connection
    $stmt_order->close();
    $conn->close();
} else {
    echo "Order ID not found.";
}
