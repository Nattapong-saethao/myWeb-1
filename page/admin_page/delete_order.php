<?php
session_start();
include '../php_script/db_connection.php';

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    try {
        // เริ่ม Transaction
        $conn->begin_transaction();

        // ลบข้อมูลในตาราง orderdetail ก่อน
        $sql_delete_orderdetail = "DELETE FROM orderdetail WHERE order_id = ?";
        $stmt_delete_orderdetail = $conn->prepare($sql_delete_orderdetail);
        $stmt_delete_orderdetail->bind_param("i", $order_id);
        $stmt_delete_orderdetail->execute();

        // ลบข้อมูลในตาราง payment
        $sql_delete_payment = "DELETE FROM payment WHERE order_id = ?";
        $stmt_delete_payment = $conn->prepare($sql_delete_payment);
        $stmt_delete_payment->bind_param("i", $order_id);
        $stmt_delete_payment->execute();

        // ลบข้อมูลในตาราง cancel
        $sql_delete_cancel = "DELETE FROM cancel WHERE order_id = ?";
        $stmt_delete_cancel = $conn->prepare($sql_delete_cancel);
        $stmt_delete_cancel->bind_param("i", $order_id);
        $stmt_delete_cancel->execute();

        // ลบข้อมูลในตาราง orders
        $sql_delete_order = "DELETE FROM orders WHERE order_id = ?";
        $stmt_delete_order = $conn->prepare($sql_delete_order);
        $stmt_delete_order->bind_param("i", $order_id);
        $stmt_delete_order->execute();

        // Commit transaction
        $conn->commit();

        echo "<p>ลบคำสั่งซื้อสำเร็จ</p>";
        header("Location: pending.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // Rollback transaction หากมีข้อผิดพลาด
        $conn->rollback();
        echo "Error deleting order: " . $e->getMessage();
    }
    $stmt_delete_orderdetail->close();
    $stmt_delete_payment->close();
    $stmt_delete_cancel->close();
    $stmt_delete_order->close();
    $conn->close();
} else {
    echo "<p>ไม่พบ order_id</p>";
}
