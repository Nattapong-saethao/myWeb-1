<?php
session_start(); // start session
require '../../vendor/autoload.php'; // ปรับ path ให้ถูกต้อง
include '../php_script/db_connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// กำหนดข้อมูลบัญชี (ควรนำไปเก็บในที่ปลอดภัย)
$accountNumber = '3904740416'; // ใส่เลขบัญชีของคุณ


$_SESSION['order_id'] = $_POST['order_id'];
$_SESSION['member_id'] = $_SESSION['user_id'];
$_SESSION['total_price'] = $_POST['total_price'];
$_SESSION['payment_method'] = $_POST['payment_method'];
if (!isset($_SESSION['order_id']) || !isset($_SESSION['member_id']) || !isset($_SESSION['total_price']) || !isset($_SESSION['payment_method'])) {

    //header("Location: checkout.php");
    exit();
}


// สร้าง payload สำหรับ Thai QR Payment
$payload = generateThaiQRPayload($accountNumber, $_SESSION['total_price']);

// กำหนดค่า Options สำหรับ QR Code
$options = new QROptions([
    'version'    => 5,
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_L,
    'scale'      => 5,
]);

// สร้าง QR Code
$qrCode = new QRCode($options);
$qrImage = $qrCode->render($payload);

// สร้างชื่อไฟล์ QR Code (ควรเป็น unique)
$fileName = 'qr_' . uniqid() . '.png';


// ฟังก์ชันสำหรับสร้าง Payload ของ Thai QR Payment
function generateThaiQRPayload($accountNumber, $amount)
{
    $merchantPayload = '000201'; // Payload Format Indicator
    $merchantPayload .= '010212'; // Point of Initiation Method
    $merchantPayload .= '29' . str_pad(strlen('0016A000000677010111') + 2, 2, '0', STR_PAD_LEFT) . '0016A000000677010111'; // Global Unique Identifier
    $merchantPayload .= '30' . str_pad(strlen('0016' . $accountNumber) + 2, 2, '0', STR_PAD_LEFT) . '0016' . $accountNumber; // Merchant Account Information
    $merchantPayload .= '5303764'; // Currency Code (THB)
    $merchantPayload .= '54' . str_pad(strlen(number_format($amount, 2, '.', '')) + 2, 2, '0', STR_PAD_LEFT) . number_format($amount, 2, '.', ''); // Transaction Amount
    $merchantPayload .= '5802TH'; // Country Code
    $merchantPayload .= '6304'; // CRC Length
    $crc = strtoupper(dechex(crc16($merchantPayload)));
    $merchantPayload .= $crc;

    return $merchantPayload;
}

// ฟังก์ชันสำหรับคำนวณ CRC16 MODBUS
function crc16($string)
{
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($string); $i++) {
        $crc ^= ord($string[$i]);
        for ($j = 8; $j != 0; $j--) {
            if (($crc & 0x0001) != 0) {
                $crc >>= 1;
                $crc ^= 0xA001;
            } else {
                $crc >>= 1;
            }
        }
    }
    return $crc;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <!-- Navbar -->
    <?php
    include 'navbar.php';
    $order_id = $_SESSION['order_id'] ?? null;
    if (!$order_id) {
        echo "Error: order_id not found.";
        exit();
    }
    ?>
    <div class="content" style="height: 750px;">
        <div class="qr-code">
            <img src="<?php echo  '/myWeb/page/admin_page/uploads/qr_image/QRcode.jpg' ?>" alt="Thai QR Payment">
        </div>
        <div class="payment-details" style="margin-top: 12%;">
            <p class="text-center">ยอดที่ต้องชำระ:</p>
            <p class="text-center">ราคาทั้งหมด <?php echo number_format($_SESSION['total_price'], 2); ?> บาท</p>
        </div>
    </div>
    <div class="container " style="background-color: #f5f5f5;border-radius: 10px;">
        <h2 class="text-center mt-4">อัปโหลดหลักฐานการชำระเงิน</h2>
        <form action="ordertracking1.php" method="POST" enctype="multipart/form-data">
            <!-- หมายเลขคำสั่งซื้อ -->
            <div class="form-group">
                <label for="order_id">หมายเลขคำสั่งซื้อ:</label>
                <input type="text" name="order_id" id="order_id" value="<?php echo htmlspecialchars($order_id); ?>" class="form-control" readonly>
            </div>

            <!-- ชื่อผู้สั่งซื้อ -->
            <div class="form-group">
                <?php
                if (isset($_SESSION['member_id'])) {
                    $sql_member = "SELECT username, surname FROM member WHERE member_id = ?";
                    $stmt_member = $conn->prepare($sql_member);
                    if ($stmt_member === false) {
                        die("Error preparing statement for member data: " . $conn->error);
                    }
                    $stmt_member->bind_param("i", $_SESSION['member_id']);
                    $stmt_member->execute();
                    $result_member = $stmt_member->get_result();

                    if ($result_member->num_rows > 0) {
                        $member = $result_member->fetch_assoc();
                        $username = $member['username'];
                        $surname = $member['surname'];
                        echo ' <label for="name">ชื่อผู้สั่งซื้อ:</label>' . htmlspecialchars($username . ' ' . $surname);
                    }
                    $stmt_member->close();
                }
                ?>

            </div>

            <!-- วันที่และเวลาโอนเงิน -->
            <div class="form-group">
                <label for="transfer_date">วันที่โอนเงิน</label>
                <input type="date" class="form-control" id="transfer_date" name="transfer_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="transfer_time">เวลาโอนเงิน</label>
                <input type="time" class="form-control" id="transfer_time" name="transfer_time" value="<?php echo date('H:i'); ?>">
            </div>

            <!-- ธนาคารต้นทาง -->
            <div class="form-group">
                <label for="bank">ธนาคารต้นทาง</label>
                <select class="form-control" id="bank" name="bank">
                    <option value="">กรุณาเลือกธนาคาร</option>
                    <option value="ธนาคารกสิกรไทย">ธนาคารกสิกรไทย</option>
                    <option value="ธนาคารไทยพาณิชย์">ธนาคารไทยพาณิชย์</option>
                    <option value="ธนาคารกรุงเทพ">ธนาคารกรุงเทพ</option>
                    <option value="ธนาคารกรุงไทย">ธนาคารกรุงไทย</option>
                    <option value="อื่น ๆ">อื่น ๆ</option>
                </select>
            </div>

            <!-- หมายเลขอ้างอิง -->
            <div class="form-group">
                <label for="reference_number">หมายเลขอ้างอิง</label>
                <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="กรุณากรอกหมายเลขอ้างอิง">
            </div>

            <!-- อัปโหลดสลิปการโอนเงิน -->
            <div class="form-group">
                <label for="payment_slip">อัปโหลดสลิปการโอนเงิน</label>
                <input type="file" class="form-control-file" id="payment_slip" name="payment_slip" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <!-- ปุ่มส่งข้อมูล -->
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6" style="margin-top: -37px;">
                        <div class="form-group">

                            <!-- ปุ่มส่งข้อมูล -->
                            <button class="btn btn-complete btn-block" type="submit" name="submit">เสร็จสิ้น</button>

                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="form-group text-center">
            <button class="btn btn-secondary mt-4 w-50" onclick="window.location.href = 'checkout.php';">ย้อนกลับ</button>
        </div>
    </div>


    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<style>
    .content {
        margin: 50px auto;
        max-width: 800px;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .qr-code {
        text-align: center;
    }

    .qr-code img {
        height: 510px;
        max-width: 650px;
    }

    .btn-complete {
        background-color: #8b5e3c;
        color: #fff;
    }

    .mt-4,
    .my-4 {
        margin-top: -0.5rem !important;
    }
</style>