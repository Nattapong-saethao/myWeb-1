<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include '../php_script/db_connection.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit;
}

// รับข้อมูล JSON
$data = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบข้อมูลที่ได้รับ
if (!isset($data['orderdetail_id']) || !isset($data['amount']) || !isset($data['price'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$orderdetail_id = intval($data['orderdetail_id']);
$amount = intval($data['amount']);
$price = floatval($data['price']);

// ตรวจสอบค่าที่ได้รับ
if ($orderdetail_id <= 0 || $amount < 0 || $price < 0) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

// ตรวจสอบว่ามีค่า selected หรือไม่ ถ้ามีให้อัปเดตด้วย
$selected = isset($data['select']) ? intval($data['select']) : null;

// เริ่มทำ transaction
$conn->begin_transaction();

try {
    // อัปเดตจำนวนและราคารวมในตาราง orderdetail
    $stmt = $conn->prepare("UPDATE orderdetail SET amount = ?, price = ? WHERE orderdetail_id = ?");
    $stmt->bind_param("idi", $amount, $price, $orderdetail_id);

    if (!$stmt->execute()) {
        throw new Exception("ไม่สามารถอัปเดตจำนวนและราคารวมสินค้าได้: " . $stmt->error);
    }

    $stmt->close();

    // อัปเดตค่า selected หากมี
    if ($selected !== null) {
        $stmt = $conn->prepare("UPDATE orderdetail SET `selected` = ? WHERE orderdetail_id = ?");
        $stmt->bind_param("ii", $selected, $orderdetail_id);
        if (!$stmt->execute()) {
            throw new Exception("ไม่สามารถอัปเดตสถานะการเลือกสินค้าได้: " . $stmt->error);
        }
        $stmt->close();
    }

    // Commit transaction หากทุกอย่างสำเร็จ
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'อัปเดตสำเร็จ']);
} catch (Exception $e) {
    // Rollback หากเกิดข้อผิดพลาด
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
