<?php
// เชื่อมต่อฐานข้อมูล
session_start();
include '../php_script/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์มเมื่อกดปุ่มบันทึก
    if (isset($_POST['cancel_detail'])) {
        $order_id = $_POST['order_id'];
        $cancel_detail = $_POST['cancel_detail'];
        $cancel_date = date('Y-m-d H:i:s'); // วันเวลาปัจจุบัน

        // ตรวจสอบว่ามีข้อมูลในตาราง cancel แล้วหรือไม่
        $check_query = "SELECT * FROM cancel WHERE order_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // หากมีข้อมูลอยู่แล้ว ให้ทำการอัปเดต
            $update_cancel_query = "UPDATE cancel SET cancel_detail = ?, cancel_date = ? WHERE order_id = ?";
            $stmt = $conn->prepare($update_cancel_query);
            $stmt->bind_param("ssi", $cancel_detail, $cancel_date, $order_id);
        } else {
            // หากไม่มีข้อมูล ให้เพิ่มใหม่
            $insert_cancel_query = "INSERT INTO cancel (order_id, cancel_detail, cancel_date) 
                                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_cancel_query);
            $stmt->bind_param("iss", $order_id, $cancel_detail, $cancel_date);
        }

        if ($stmt->execute()) {
            // อัปเดตสถานะของตาราง orders
            $update_order_query = "UPDATE orders SET status = 'cancel' WHERE order_id = ?";
            $stmt = $conn->prepare($update_order_query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            header("Location: history.php");
        } else {
            echo "เกิดข้อผิดพลาด: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยกเลิกคำสั่งซื้อ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    ?>
    <div class="container mt-4" style="background-color: #f5f5f5; border-radius: 10px;">
        <h2>ยกเลิกคำสั่งซื้อ</h2>
        <form method="POST" action="cancel_order.php">
            <input type="hidden" name="order_id" value="<?php echo $_POST['order_id']; ?>">
            <label for="cancel_detail">รายละเอียดการยกเลิก/เหตุผล:</label><br>
            <textarea name="cancel_detail" id="cancel_detail" rows="7" cols="100" required></textarea><br><br>
            <button type="submit" class="btn btn-primary mt-4 w-50">บันทึก</button>

        </form>
        <button type="button" class="btn btn-secondary mt-4 w-50" onclick="window.location.href = 'history.php';">ย้อนกลับ</button>
    </div>
</body>

</html>