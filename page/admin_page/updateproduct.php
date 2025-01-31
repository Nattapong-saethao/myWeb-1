<?php
session_start();
include '../php_script/db_connection.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $category_id = $_POST['category_id'];
    $product_name = $_POST['productName'];
    $product_details = $_POST['productDetail'];
    $new_quantity = (int)$_POST['quantity']; // Convert to integer
    $price = $_POST['price'];
    $adjustment_detail = "";
    $user_id = $_SESSION['user_id'];

    // ดึงข้อมูลสินค้าเก่า
    $sql = "SELECT amount FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($old_quantity);
    $stmt->fetch();
    $stmt->close();

    // ตรวจสอบว่ามีไฟล์ภาพหรือไม่
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp = $image['tmp_name'];
        $image_path = "uploads/" . $image_name;
        move_uploaded_file($image_tmp, $image_path);

        $sql = "UPDATE product SET category_id = ?, product_name = ?, product_detail = ?, amount = ?, price = ?, image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issidsi", $category_id, $product_name, $product_details, $new_quantity, $price, $image_path, $product_id);
    } else {
        $sql = "UPDATE product SET category_id = ?, product_name = ?, product_detail = ?, amount = ?, price = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsi", $category_id, $product_name, $product_details, $new_quantity, $price, $product_id);
    }


    if ($stmt->execute()) {
        // บันทึกการปรับปรุงสต็อก

        if ($new_quantity > (int)$old_quantity) {
            $adjustment_detail = "เพิ่มจำนวนสินค้า";
        } elseif ($new_quantity < (int)$old_quantity) {
            $adjustment_detail = "ปรับลดจำนวนสินค้า";
        } else {
            $adjustment_detail = "ไม่ได้ปรับปรุงจำนวนสินค้า";
        }

        $sql = "INSERT INTO stockadjustment (user_id, product_id, adjustment_detail, amount, adjustment_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $user_id, $product_id, $adjustment_detail, $new_quantity);
        $stmt->execute();

        $response['message'] = 'แก้ไขสินค้าสำเร็จ';
    } else {
        $response['message'] = "เกิดข้อผิดพลาดในการแก้ไขสินค้า";
    }

    $stmt->close();
} else {
    $response['message'] = "Invalid request.";
}
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
