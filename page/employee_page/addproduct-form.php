<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    ?>
    <div class="content">
        <h2>เพิ่มสินค้า</h2>
        <form id="addProductForm" enctype="multipart/form-data">
            <?php
            include '../php_script/db_connection.php';

            // ดึงข้อมูลประเภทสินค้าจากตาราง productcategory
            try {
                $sql = "SELECT category_id, category_name FROM productcategory ORDER BY category_id";
                $stmt_category = $conn->prepare($sql);
                $stmt_category->execute();
                $result_category = $stmt_category->get_result();
                $categories = $result_category->fetch_all(MYSQLI_ASSOC);
                $stmt_category->close();
            } catch (mysqli_sql_exception $e) {
                echo "Database Error: " . $e->getMessage();
                die();
            }

            // ดึงข้อมูลหน่วยนับจากตาราง Unit
            try {
                $sql = "SELECT unit_id, unit_name FROM units ORDER BY unit_id";
                $stmt_unit = $conn->prepare($sql);
                $stmt_unit->execute();
                $result_unit = $stmt_unit->get_result();
                $units = $result_unit->fetch_all(MYSQLI_ASSOC);
                $stmt_unit->close();
            } catch (mysqli_sql_exception $e) {
                echo "Database Error: " . $e->getMessage();
                die();
            }
            ?>

            <!-- ส่วนของ HTML -->
            <div class="form-group">
                <label>ประเภทสินค้า</label><br>
                <?php if ($categories): ?>
                    <?php foreach ($categories as $category): ?>
                        <input type="radio" id="category_<?php echo $category['category_id']; ?>" name="productType" value="<?php echo $category['category_id']; ?>" required>
                        <label for="category_<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>ไม่มีประเภทสินค้า</p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="productName">ชื่อสินค้า</label>
                <input type="text" class="form-control" id="productName" name="productName" required>
            </div>
            <div class="form-group">
                <label for="productDetails">รายละเอียดสินค้า</label>
                <textarea class="form-control" id="productDetails" name="productDetails" required></textarea>
            </div>
            <div class="form-group">
                <label for="unit">หน่วยนับ</label>
                <select class="form-control" id="unit" name="unit" required>
                    <option value="">เลือกหน่วยนับ</option>
                    <?php if ($units): ?>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?php echo $unit['unit_id']; ?>"><?php echo htmlspecialchars($unit['unit_name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>ไม่มีหน่วยนับ</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">จำนวน</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="form-group">
                <label for="price">ราคา</label>
                <input type="text" class="form-control" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="image">รูปภาพ</label>
                <input type="file" class="form-control-file" id="image" name="image" required>
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
            $('#addProductForm').on('submit', function(e) {
                e.preventDefault(); // ป้องกันการ submit แบบปกติ

                var formData = new FormData(this); // เก็บข้อมูลฟอร์ม

                $.ajax({
                    url: '/myWeb/page/admin_page/addproduct.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        var result = JSON.parse(response);
                        $('#resultMessage').text(result.message);
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
                location.reload(); // รีเฟรชหน้าเว็บ
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
        background-color: #f8f9fa;
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