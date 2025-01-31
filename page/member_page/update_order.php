<?php
// update_order_status.php
include '../php_script/db_connection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// ตรวจสอบว่ามีการส่ง request แบบ POST มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่ามี order_id และ status ถูกส่งมาหรือไม่
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];



        // ตรวจสอบสถานะปัจจุบันของ order ก่อนทำการ update (เพื่อความปลอดภัย)
        $sql_check = "SELECT status FROM orders WHERE order_id = '$order_id'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            $row_check = $result_check->fetch_assoc();
            $current_status = $row_check['status'];

            // ตรวจสอบว่าสถานะปัจจุบันเป็น "wait" หรือไม่ (ตามเงื่อนไขที่กำหนด)
            if ($current_status == "wait") {
                // Update สถานะเป็น "pending"
                $sql_update = "UPDATE orders SET status = 'pending' WHERE order_id = '$order_id'";

                if ($conn->query($sql_update) === TRUE) {
                    // ส่ง response กลับไปเป็น JSON (สำคัญ!)
                    $response = array("status" => "success", "message" => "Order status updated to pending successfully.");
                    echo json_encode($response);
                } else {
                    // ส่ง response กลับไปเป็น JSON (สำคัญ!)
                    $response = array("status" => "error", "message" => "Error updating record: " . $conn->error);
                    echo json_encode($response);
                }
            } else {
                // ส่ง response กลับไปเป็น JSON (สำคัญ!)
                $response = array("status" => "warning", "message" => "Order status cannot be updated. Current status is: " . $current_status);
                echo json_encode($response);
            }
        } else {
            // ส่ง response กลับไปเป็น JSON (สำคัญ!)
            $response = array("status" => "error", "message" => "Order not found.");
            echo json_encode($response);
        }

        $conn->close();
    } else {
        // ส่ง response กลับไปเป็น JSON (สำคัญ!)
        $response = array("status" => "error", "message" => "Missing order ID or status.");
        echo json_encode($response);
    }
} else {
    // ถ้าไม่ใช่ POST request ให้ส่ง error
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
