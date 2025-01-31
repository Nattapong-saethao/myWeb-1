<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<div class="sidebar">
    <div class="admin-info text-center mb-5">
        <p>
            <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
        </p>
        <p><?php echo $_SESSION['username']; ?></p>
    </div>
    <a href="stockproduct.php" onclick="hideSubMenu()">จัดการสต็อกสินค้า</a>
    <a onclick="event.preventDefault(); showSubMenu();">จัดการคำสั่งซื้อ</a>
    <ul id="subMenu" class="sub-menu" style="display:none;">
        <li><a href="order.php" onclick="hideSubMenu()">คำสั่งซื้อที่จัดเตรียมสินค้า</a></li>
        <li><a href="order_shipped.php" onclick="hideSubMenu()">คำสั่งซื้ออยู่ระหว่างจัดส่ง</a></li>
        <li><a href="order_complet.php" onclick="hideSubMenu()">คำสั่งซื้อที่เสร็จสิ้น</a></li>
    </ul>
    <a href="employemanage.php" onclick="hideSubMenu()">จัดการข้อมูลผู้ใช้</a>
    <a href="../admin_page/logout.php" onclick="hideSubMenu()">ออกจากระบบ</a>
</div>
<script>
    function showSubMenu() {
        var subMenu = document.getElementById("subMenu");
        subMenu.style.display = "block";
    }
    
    function hideSubMenu() {
        var subMenu = document.getElementById("subMenu");
        subMenu.style.display = "none";
    }
</script>
<style>
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
</style>