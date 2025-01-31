<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee De Hmong</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php
    session_start();
    include 'navbar.php';
    ?>

    <div class="container mt-5">
        <h2 style="text-align: center;line-height: 45px;">ติดต่อสอบถาม / แจ้งปัญหาการใช้งาน</h2>
        <form action="submit_form.php" method="post">
            <div class="form-group">
                <label for="details">รายละเอียด :</label>
                <textarea class="form-control" id="details" name="details" rows="3" style="max-width: 656px;height: 170px;"></textarea>
            </div>
            <div class="form-group">
                <label for="name">ชื่อ :</label>
                <input type="text" class="form-control" id="name" name="name" style="max-width: 656px;">
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" class="form-control" id="email" name="email" style="max-width: 656px;">
            </div>
            <button type="submit" class="btn btn-primary">ส่ง</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-image: url('https://lh3.googleusercontent.com/pw/AP1GczPmqGq2T3n-nAvYLQpYY4XGfGmYrG6e14dZMwwmHvDvy2qGxTQ2ym1rF5_M-evhZecc_TfzT9_Ho2fJuG8KOzX45Sou0p5mb-16hew6b5pD_9hj3xuSCSSpD5GzhR8KwBVd9whUDH-scMmD1uNGNhxh=w643-h360-s-no-gm?authuser=0');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;

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

    .container.mt-5 {
        margin-top: 50px;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        height: 550px;
    }

    .btn-primary {
        position: fixed;
        padding: auto;
        right: 47%;
    }

    .btn-primary {
        position: fixed;
        right: 51%;
    }
</style>