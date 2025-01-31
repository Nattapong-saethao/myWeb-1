<?php
session_start();
include '../php_script/db_connection.php';

// ตรวจสอบว่ามีการส่ง category_id มาหรือไม่
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    // ป้องกัน SQL Injection โดยใช้ Prepared Statement
    try {
        // เริ่ม transaction เพื่อป้องกันข้อมูลเสียหายหากเกิดข้อผิดพลาด
        $conn->begin_transaction();

        // ลบข้อมูลในตาราง product ที่มี category_id นี้
        $sql_delete_product = "DELETE FROM product WHERE category_id = ?";
        $stmt_delete_product = $conn->prepare($sql_delete_product);
        $stmt_delete_product->bind_param("i", $category_id);

        if ($stmt_delete_product->execute()) {
            // ลบข้อมูลในตาราง productcategory
            $sql_delete_category = "DELETE FROM productcategory WHERE category_id = ?";
            $stmt_delete_category = $conn->prepare($sql_delete_category);
            $stmt_delete_category->bind_param("i", $category_id);

            if ($stmt_delete_category->execute()) {
                // commit transaction หากไม่มีข้อผิดพลาด
                $conn->commit();

                $_SESSION['delete_message'] = "ลบประเภทสินค้าสำเร็จ"; // เก็บข้อความใน session

            } else {
                $conn->rollback();
                $_SESSION['delete_message'] = "เกิดข้อผิดพลาดในการลบประเภทสินค้า: " . $stmt_delete_category->error;
            }
            $stmt_delete_category->close();
        } else {
            $conn->rollback();
            $_SESSION['delete_message'] = "เกิดข้อผิดพลาดในการลบข้อมูลสินค้าที่เกี่ยวข้อง: " . $stmt_delete_product->error;
        }

        $stmt_delete_product->close();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $_SESSION['delete_message'] = "Database Error: " . $e->getMessage();
    }
} else {
    $_SESSION['delete_message'] = "ไม่พบ category_id ที่ต้องการลบ";
}

$conn->close();
header("Location: manage_category.php"); // Redirect กลับไปหน้า manage category
exit; // จบการทำงานของ script
