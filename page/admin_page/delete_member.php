<?php
session_start();
include '../php_script/db_connection.php';

if (isset($_GET['id'])) {
    $member_id = $_GET['id'];

    try {
        // เริ่ม Transaction
        $conn->begin_transaction();

        // ลบข้อมูลในตาราง orders ที่มี member_id นี้
        $sql_delete_orders = "DELETE FROM orders WHERE member_id = ?";
        $stmt_delete_orders = $conn->prepare($sql_delete_orders);
        $stmt_delete_orders->bind_param("i", $member_id);
        $stmt_delete_orders->execute();

        // ลบข้อมูลในตาราง stockadjustment ที่มี user_id นี้
        $sql_delete_stockadjustment = "DELETE FROM stockadjustment WHERE user_id = ?";
        $stmt_delete_stockadjustment = $conn->prepare($sql_delete_stockadjustment);
        $stmt_delete_stockadjustment->bind_param("i", $member_id);
        $stmt_delete_stockadjustment->execute();


        // ลบข้อมูลในตาราง member
        $sql_delete_member = "DELETE FROM member WHERE member_id = ?";
        $stmt_delete_member = $conn->prepare($sql_delete_member);
        $stmt_delete_member->bind_param("i", $member_id);
        $stmt_delete_member->execute();


        // Commit transaction
        $conn->commit();

        echo "<p>ลบข้อมูลสมาชิกสำเร็จ</p>";
        header("Location: membermanage.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // Rollback transaction หากมีข้อผิดพลาด
        $conn->rollback();
        echo "Error deleting member: " . $e->getMessage();
    }
    $stmt_delete_orders->close();
    $stmt_delete_stockadjustment->close();
    $stmt_delete_member->close();
    $conn->close();
} else {
    echo "<p>ไม่พบ member_id</p>";
}
