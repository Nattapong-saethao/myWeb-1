<?php
session_start();
include '../php_script/db_connection.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        // เริ่ม Transaction
        $conn->begin_transaction();

        // ลบข้อมูลในตาราง stockadjustment ที่มี user_id นี้
        $sql_delete_stockadjustment = "DELETE FROM stockadjustment WHERE user_id = ?";
        $stmt_delete_stockadjustment = $conn->prepare($sql_delete_stockadjustment);
        $stmt_delete_stockadjustment->bind_param("i", $user_id);
        $stmt_delete_stockadjustment->execute();

        // ลบข้อมูลในตาราง _user
        $sql_delete_user = "DELETE FROM _user WHERE user_id = ?";
        $stmt_delete_user = $conn->prepare($sql_delete_user);
        $stmt_delete_user->bind_param("i", $user_id);
        $stmt_delete_user->execute();

        // Commit transaction
        $conn->commit();

        echo "<p>ลบข้อมูลพนักงานสำเร็จ</p>";
        header("Location: employemanage.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // Rollback transaction หากมีข้อผิดพลาด
        $conn->rollback();
        echo "Error deleting employee: " . $e->getMessage();
    }
    $stmt_delete_stockadjustment->close();
    $stmt_delete_user->close();
    $conn->close();
} else {
    echo "<p>ไม่พบ user_id</p>";
}
