<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <?php
    session_start();
    include('sidebar.php');

    include '../php_script/db_connection.php';

    // ตรวจสอบว่ามี product_id ส่งมาหรือไม่
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        echo "Product ID is missing.";
        exit;
    }
    $product_id = $_POST['product_id'];

    // ดึงข้อมูลสินค้าจากฐานข้อมูล
    try {
        $sql = "SELECT product_name, category_id, product_detail, amount, price, image FROM product WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            echo "Product not found.";
            exit;
        }
        $product = $result->fetch_assoc();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database Error: " . $e->getMessage();
        die();
    }

    ?>
    <div class="content">
        <h2>แก้ไขสินค้า</h2>
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <div class="form-group">
                <label>ประเภทสินค้า</label><br>
                <select class="form-control" name="category_id" required>
                    <option value="1" <?php echo ($product['category_id'] == 1) ? 'selected' : ''; ?>>ผลิตภัณฑ์กาแฟ</option>
                    <option value="2" <?php echo ($product['category_id'] == 2) ? 'selected' : ''; ?>>ผลไม้</option>
                </select>

            </div>
            <div class="form-group">
                <label for="productName">ชื่อสินค้า</label>
                <input type="text" class="form-control" id="productName" name="productName" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="productDetails">รายละเอียดสินค้า</label>
                <textarea class="form-control" id="productDetail" name="productDetail" required><?php echo htmlspecialchars($product['product_detail']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="quantity">จำนวน</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['amount']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">ราคา</label>
                <input type="text" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image">รูปภาพ</label><br>
                <?php if ($product['image']): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" class="img-fluid" style="max-width: 35%">>
                <?php else: ?>
                    <p>No image</p>
                <?php endif; ?>
                <input type="file" class="form-control-file" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-success">บันทึก</button>
        </form>

        <!-- Popup Modal -->
        <div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ผลการดำเนินการ</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="resultMessage"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#editProductForm').on('submit', function(e) {
                e.preventDefault(); // ป้องกันการ submit แบบปกติ

                var formData = new FormData(this); // เก็บข้อมูลฟอร์ม

                $.ajax({
                    url: 'updateproduct.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json', // บอกให้ jQuery parse response เป็น JSON
                    success: function(response) {
                        console.log(response);
                        $('#resultMessage').text(response.message);
                        $('#resultModal').modal('show');
                    },
                    error: function() {
                        $('#resultMessage').text('เกิดข้อผิดพลาดในการส่งข้อมูล');
                        $('#resultModal').modal('show');
                    }
                });
            });

            // เมื่อ modal ถูกปิด ให้รีเฟรชหน้า
            $('#resultModal').on('hidden.bs.modal', function() {
                window.location.href = '/myWeb/page/admin_page/manageproduct.php';
            });
        });
    </script>
</body>

</html>
<style>
    body {
        display: flex;
    }

    .sidebar {
        position: fixed;
        /* ทำให้ sidebar คงที่ */
        top: 0;
        left: 0;
        height: 100vh;
        /* ความสูงเท่ากับ 100% ของ viewport */
        width: 200px;
        /* กำหนดความกว้างของ sidebar */
        background-color: #343a40;
        /* สีพื้นหลัง */
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar a {
        display: block;
        padding: 10px;
        color: #333;
        text-decoration: none;
    }

    .sidebar a:hover {
        background-color: #ddd;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: absolute;
            height: auto;
            width: 100%;
        }

        .content {
            margin-left: 0;
            width: 100%;
        }
    }

    .content {
        margin-left: 200px;
        /* เว้นระยะทางซ้ายให้เท่ากับ sidebar */
        padding: 20px;
        width: calc(100% - 200px);
        /* คำนวณความกว้างใหม่ให้พอดีกับหน้าจอ */
        box-sizing: border-box;
        /* รวม padding ไว้ในการคำนวณ width */
    }
</style>