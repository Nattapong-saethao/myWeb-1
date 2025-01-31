<?php
include '../php_script/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();

    // 1. รับค่าจาก form
    $category_id = $_POST['productType'];
    $product_name = $_POST['productName'];
    $product_detail = $_POST['productDetails'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $unit_id = $_POST['unit_id']; // เปลี่ยนชื่อตัวแปรจาก $units เป็น $unit_id

    // 2. จัดการรูปภาพ
    $image = $_FILES['image'];
    $target_dir = "uploads/";

    // 2.1 ตรวจสอบว่ามีไฟล์ถูกอัปโหลดหรือไม่
    if ($image['error'] === UPLOAD_ERR_NO_FILE) {
        $response['status'] = 'error';
        $response['message'] = 'กรุณาเลือกรูปภาพ';
        echo json_encode($response);
        exit();
    }

    // 2.2 เช็คว่ามี error อื่นๆในการอัปโหลดหรือไม่
    if ($image['error'] !== UPLOAD_ERR_OK) {
        $response['status'] = 'error';
        $response['message'] = 'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ: ' . $image['error'];
        echo json_encode($response);
        exit();
    }

    $image_name = basename($image['name']);
    $target_file = $target_dir . $image_name;
    $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // get file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif'); // allowed file types

    // 2.3 ตรวจสอบประเภทไฟล์
    if (!in_array($image_type, $allowed_types)) {
        $response['status'] = 'error';
        $response['message'] = 'อนุญาติเฉพาะไฟล์รูปภาพ JPG, JPEG, PNG, และ GIF เท่านั้น';
        echo json_encode($response);
        exit();
    }
    // 2.4 ตรวจสอบขนาดไฟล์
    if ($image['size'] > 5000000) {
        $response['status'] = 'error';
        $response['message'] = 'ขนาดไฟล์รูปภาพต้องไม่เกิน 5MB';
        echo json_encode($response);
        exit();
    }

    // 3. Move รูปภาพ
    if (move_uploaded_file($image['tmp_name'], $target_file)) {
        // 4. Insert ข้อมูล (ใช้ Prepared Statement เพื่อป้องกัน SQL Injection)
        $sql = "INSERT INTO Product (category_id, product_name, product_detail, price, amount, image, unit_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        // แก้ไขให้ bind_param ใช้ตัวแปร $unit_id ที่รับค่าจาก form
        if ($unit_id === null) {
            $stmt->bind_param("issdiss", $category_id, $product_name, $product_detail, $price, $quantity, $target_file, $unit_id);
        } else {
            $stmt->bind_param("issdisi", $category_id, $product_name, $product_detail, $price, $quantity, $target_file, $unit_id);
        }

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'เพิ่มสินค้าสำเร็จ';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ';
    }

    $conn->close();
    echo json_encode($response);
}
