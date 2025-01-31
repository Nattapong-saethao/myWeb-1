<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../php_script/db_connection.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $member_id = $_POST['member_id'] ?? null;
    $total_price = $_POST['total_price'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    if ($order_id && $member_id && $total_price && $payment_method) {
        // Get payment time
        $payment_date = date('Y-m-d H:i:s');

        // Convert total price to float
        $total_price = floatval($total_price);

        // Check if a record with this order_id already exists
        $check_sql = "SELECT COUNT(*) FROM payment WHERE order_id = ?";
        $check_stmt = $conn->prepare($check_sql);

        if ($check_stmt) {
            $check_stmt->bind_param("i", $order_id);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                // Record exists, perform update
                $update_sql = "UPDATE payment SET member_id = ?, payment_date = ?, price = ?, payment_method = ? WHERE order_id = ?";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt) {
                    $update_stmt->bind_param("isdsi", $member_id, $payment_date, $total_price, $payment_method, $order_id);
                    if ($update_stmt->execute()) {
                        echo "Update success"; // Send response text that has success status.
                        http_response_code(200);
                    } else {
                        echo 'Error execute update query: ' . $update_stmt->error;
                        http_response_code(500);
                    }
                    $update_stmt->close();
                } else {
                    echo "Error in update statement prepare: " . $conn->error;
                    http_response_code(500);
                }
            } else {
                // Record does not exist, perform insert
                $insert_sql = "INSERT INTO payment (member_id, order_id, payment_date, price, payment_method) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);

                if ($insert_stmt) {
                    $insert_stmt->bind_param("iisds", $member_id, $order_id, $payment_date, $total_price, $payment_method);
                    if ($insert_stmt->execute()) {
                        echo "Insert success";
                        http_response_code(200);
                    } else {
                        echo 'Error execute insert query: ' . $insert_stmt->error;
                        http_response_code(500);
                    }
                    $insert_stmt->close();
                } else {
                    echo "Error in insert statement prepare: " . $conn->error;
                    http_response_code(500);
                }
            }
        } else {
            echo "Error in check statement prepare: " . $conn->error;
            http_response_code(500);
        }
    } else {
        echo 'Error please check input data';
        http_response_code(400);
    }
    $conn->close();
} else {
    echo 'Invalid Method ';
    http_response_code(405);
}
