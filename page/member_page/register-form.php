<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-5">
        <div class="register-container p-4 border rounded">
            <h3 class="text-center mb-4">ลงทะเบียนเข้าใช้งาน</h3>
            <form id="registerForm" method="POST" action="register.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Name/ชื่อ</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter Name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="surname" class="form-label">Surname/นามสกุล</label>
                        <input type="text" class="form-control" id="surname" name="surname" placeholder="Enter Surname" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number/หมายเลขโทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email/อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password/รหัสผ่าน</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="address" class="form-label">Address/ที่อยู่</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter Address" required></textarea>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" onclick="window.location.href='login.php'">ลงทะเบียน</button>
                </div>
            </form>
        </div>
    </div>

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

    .register-container {
        max-width: 700px;
        margin: 100px auto;
        padding: 20px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .register-btn {
        background-color: #5b3c1e;
        color: #fff;
        border: none;
    }

    .register-btn:hover {
        background-color: #4a2e16;
    }
</style>