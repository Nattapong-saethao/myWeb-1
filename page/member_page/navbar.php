<!DOCTYPE html>
<html>

<head>
    <title>Your Page Title</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#"><img src="/myWeb/image/logo.png" alt="Coffee De Hmong" style="width: 390px ;height: 60px;"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="home.php">หน้าหลัก</a></li>

                <?php
                include '../php_script/db_connection.php';

                ?>

                <?php
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");
                header("Expires: 0");

                if (isset($_SESSION['user_id'])) {
                    // ดึงข้อมูลประเภทสินค้าจากฐานข้อมูล
                    $sql_category = "SELECT category_id, category_name FROM productcategory";
                    $result_category = $conn->query($sql_category);


                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:void(0);" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="toggleDropdown(this)">
                            ประเภทสินค้า
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        ';

                    if ($result_category->num_rows > 0) {
                        while ($category = $result_category->fetch_assoc()) {
                            echo '
                            <a class="dropdown-item" href="product_manu.php?category_id=' . $category['category_id'] . '">
                                ' . $category['category_name'] . '
                            </a>
                        ';
                        }
                    } else {
                        echo '<span class="dropdown-item">ไม่มีประเภทสินค้า</span>';
                    }

                    echo '
                             </div>
                        </li>';
                    echo '<li class="nav-item"><a class="nav-link" href="cartproduct.php">ตะกร้าของฉัน</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="myorder.php">คำสั่งซื้อของฉัน</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="history.php">ประวัติคำสั่งซื้อของฉัน</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="editprofile-form.php">Hello, ' . $_SESSION['user_name'] . '</a></li>';
                    echo '<li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="showLogoutPopup()">ออกจากระบบ</a>
                        </li>
                        <!-- Modal -->
                        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="logoutModalLabel">คุณแน่ใจหรือไม่?</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        คุณ  ' . $_SESSION['user_name'] . ' คุณต้องการออกจากระบบจริงหรือไม่?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                        <a href="../admin_page/logout.php" type="button" class="btn btn-primary">ออกจากระบบ</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                } else {
                    echo '<li class="nav-item"><a class="nav-link" href="product_manu.php">เมนูสินค้า</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="login-form.php">เข้าสู่ระบบ</a></li>';
                }
                ?>
            </ul>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>

</html>
<script>
    function showLogoutPopup() {
        // Show the modal when the user clicks the logout link
        $('#logoutModal').modal('show');
    }

    function toggleDropdown(element) {
        $(element).dropdown('toggle');
    }
</script>
<style>
    body {
        background-image: url("/myWeb/image/background.jpg");
        background-size: cover;
        background-repeat: no-repeat;
        height: 100vh;
        background-position: center;
        font-family: 'Arial', sans-serif;
    }

    .navbar {
        background-color: #A67B5B;
    }

    .navbar-brand {
        font-weight: bold;
        color: #fff;
    }

    .navbar-nav .nav-link {
        color: rgb(0, 0, 0);
    }

    .navbar-nav .nav-link:hover {
        background-color: #A67B5B;
        color: #fff;
        border-radius: 5px;
    }
</style>