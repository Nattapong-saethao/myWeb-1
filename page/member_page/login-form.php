<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Coffee De Hmong</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    session_start();

    ?>
    <div class="login-container">
        <div class="welcome-section text-center">
            <h2>Welcome to Coffee De Hmong</h2>
            <h3>กาแฟแดงมัง</h3>
            <p style="margin-top: 100px" font-size="large">
                กาแฟปลูกใต้ร่มเงา แบบเกษตรอินทรีย์ <br></br>
                กาแฟรักษาป่า เพื่อคนอยู่คู่กับป่าอย่างยั่งยืน
            </p>
        </div>
        <div class="login-section">
            <h3 class="text-center">ลงชื่อเข้าใช้งาน</h3>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="text" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">ลงชื่อเข้าใช้</button>
                </div>
            </form>
            <p class="text-center mt-3">
                ยังไม่ได้เป็นสมาชิก? <a href="register-form.php">ลงทะเบียน</a>
            </p>
            <p class="text-center mt-3">
                ลงชื่อเข้าใช้ <a href="../employee_page/admin_login_form.php">สำหรับเจ้าของร้านและพนักงาน</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<style>
    body {
        background-image: url("/myWeb/image/background.jpg");
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        font-family: 'Arial', sans-serif;
    }

    .login-container {
        max-width: 1100px;
        margin: 21% auto;
        display: flex;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .welcome-section {
        background-color: #ECB176;
        color: white;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        padding: 30px;
        flex: 1;
    }

    .login-section {
        padding: 30px;
        flex: 1;
    }

    .login-section .btn-primary {
        background-color: #8b4513;
        border: none;
    }

    .login-section .btn-primary:hover {
        background-color: #6a3310;
    }

    .login-section a {
        color: rgb(22, 5, 255);
    }

    .login-section a:hover {
        text-decoration: underline;
    }
</style>