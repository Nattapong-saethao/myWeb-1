// cartproduct.js

document.querySelectorAll('.btn-plus').forEach(function (btn) {
    btn.addEventListener('click', function () {
        let quantityElement = this.previousElementSibling;
        let quantity = parseInt(quantityElement.innerText);
        quantityElement.innerText = quantity + 1;
        updateTotal();
    });
});

document.querySelectorAll('.btn-minus').forEach(function (btn) {
    btn.addEventListener('click', function () {
        let quantityElement = this.nextElementSibling;
        let quantity = parseInt(quantityElement.innerText);
        if (quantity > 1) { // ไม่อนุญาตให้ลดต่ำกว่า 1
            quantityElement.innerText = quantity - 1;
            updateTotal();
        }
    });
});

// ฟังก์ชันอัปเดตราคารวม
function updateTotal() {
    let total = 0;
    document.querySelectorAll('tbody tr').forEach(function (row) {
        let price = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace(' บาท', ''));
        let quantity = parseInt(row.querySelector('.quantity').innerText);
        total += price * quantity;
    });
    document.getElementById('total-price').innerText = total.toFixed(2);
}

// ส่งข้อมูลไปยัง server เพื่อลงตาราง orders และ orderdetail
function goToDetail() {
    window.location.href = 'checkout.php';
}
