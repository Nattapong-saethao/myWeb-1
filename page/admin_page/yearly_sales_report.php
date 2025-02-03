<?php
// Include your database connection here
include '../php_script/db_connection.php';

$selected_year = $_GET['year'] ?? date('Y');

$sql_report = "SELECT o.order_id, o.order_date, c.username, c.surname, SUM(od.price * od.amount) AS total_order_price
            FROM orders o
            JOIN orderdetail od ON o.order_id = od.order_id
            JOIN member c ON o.member_id = c.member_id
            WHERE YEAR(o.order_date) = ? AND o.status = 'success'
            GROUP BY o.order_id
            ORDER BY o.order_date";
$stmt = $conn->prepare($sql_report);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานยอดขายประจำปี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>รายงานยอดขายประจำปี: <?php echo $selected_year; ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ชื่อลูกค้า</th>
                    <th>ยอดรวม</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['order_id'] . "</td>";
                        echo "<td>" . $row['order_date'] . "</td>";
                        echo "<td>" . $row['username'] . " " . $row['surname'] . "</td>";
                        echo "<td>" . number_format($row['total_order_price'], 2) . " บาท</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>ไม่มีข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="javascript:history.back()" class="btn btn-secondary">กลับ</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>