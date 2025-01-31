<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<div class="sidebar">
    <div class="admin-info text-center mb-5">
        <p>
            <i class="bi bi-person-circle" style="font-size: 2rem;color: #fff;"></i>
        </p>
        <p style="color: #fff;"><?php echo $_SESSION['username']; ?></p>
    </div>
    <a href="dashboard_admin.php" onclick="closeAllSubMenus();" style="color: #fff;">ภาพรวม</a>
    <a onclick="event.preventDefault(); toggleSubMenu('subMenu');" style="color: #fff;">จัดการคำสั่งซื้อ</a>
    <ul id="subMenu" class="sub-menu" style="display: none;">
        <li><a href="neworders.php" onclick="closeAllSubMenus();" style="color: #fff;">คำสั่งที่รอตรวจสอบการชำระเงิน</a></li>
        <li><a href="pending.php" onclick="closeAllSubMenus();" style="color: #fff;">คำสั่งซื้อที่จัดเตรียมสินค้า</a></li>
        <li><a href="pickup.php" onclick="closeAllSubMenus();" style="color: #fff;">คำสั่งซื้ออยู่ระหว่างจัดส่ง</a></li>
        <li><a href="complet.php" onclick="closeAllSubMenus();" style="color: #fff;">คำสั่งซื้อที่เสร็จสิ้น</a></li>
        <li><a href="cancel.php" onclick="closeAllSubMenus();" style="color: #fff;">คำสั่งซื้อที่ถูกยกเลิก</a></li>
    </ul>
    <a href="manageproduct.php" onclick="closeAllSubMenus();" style="color: #fff;">จัดการสินค้า</a>
    <a href="manage_category.php" onclick="closeAllSubMenus();" style="color: #fff;">จัดการประเภทสินค้า</a>
    <a href="edit_units.php" onclick="closeAllSubMenus();" style="color: #fff;">จัดการหน่วยนับ</a>
    <a href="add_user_form.php" onclick="closeAllSubMenus();" style="color: #fff;">เพิ่มผู้ใช้งาน</a>
    <a href="membermanage.php" onclick="closeAllSubMenus();" style="color: #fff;">จัดการข้อมูลสมาชิก</a>
    <a href="employemanage.php" onclick="closeAllSubMenus();" style="color: #fff;">จัดการข้อมูลพนักงาน</a>
    <a href="summary.php" onclick="closeAllSubMenus();" style="color: #fff;">รายงานยอดขาย</a>
    <a href="logout.php" onclick="closeAllSubMenus();" style="color: #fff;">ออกจากระบบ</a>

    <script>
        function closeAllSubMenus() {
            const subMenus = document.querySelectorAll('.sub-menu');
            subMenus.forEach(subMenu => {
                subMenu.style.display = 'none';
            });
        }

        function toggleSubMenu(subMenuId) {
            const subMenu = document.getElementById(subMenuId);
            if (subMenu.style.display === 'none' || subMenu.style.display === '') {
                closeAllSubMenus(); // Close all other submenus first
                subMenu.style.display = 'block';
            } else {
                subMenu.style.display = 'none';
            }
        }

        // Initially hide the subMenu
        document.addEventListener("DOMContentLoaded", function() {
            closeAllSubMenus();
        });
    </script>
</div>
<style>

</style>