<?php
include '../php_script/db_connection.php';

if (isset($_GET['date'])) {
    $selectedDate = $_GET['date'];

    if ($selectedDate == 'today') {
        $sql = "SELECT COUNT(*) as order_count FROM orders WHERE DATE(order_date) = CURDATE()";
    } else {
        $sql = "SELECT COUNT(*) as order_count FROM orders WHERE DATE(order_date) = ?";
    }


    $stmt = $conn->prepare($sql);
    if ($selectedDate != 'today') {
        $stmt->bind_param("s", $selectedDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo $data['order_count'] ?? 0;
}
