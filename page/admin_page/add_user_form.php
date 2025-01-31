<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้งาน</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <?php
    session_start();
    include('sidebar.php');
    ?>

    <div class="content">
        <h2>เพิ่มผู้ใช้งาน</h2>
        <form id="adduserForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>ประเภทผู้ใช้งาน</label><br>
                <input type="radio" id="admin" name="role" value="1" required>
                <label for="admin">Admin</label>
                <input type="radio" id="employee" name="role" value="2" required>
                <label for="employee">พนักงาน</label>
            </div>
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้งาน</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="surname">นามสกุล</label>
                <input type="text" class="form-control" id="surname" name="surname" required></input>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
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
                            <span aria-hidden="true">&times;</span>
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
            $('#adduserForm').on('submit', function(e) {
                e.preventDefault(); // ป้องกันการ submit แบบปกติ

                var formData = new FormData(this); // เก็บข้อมูลฟอร์ม

                $.ajax({
                    url: 'add_user.php',
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