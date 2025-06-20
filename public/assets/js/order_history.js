function cancelOrder(orderId) {
    if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
        // Gửi AJAX request để hủy đơn hàng
        fetch('../../modules/order/cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                reason: 'Khách hàng hủy đơn'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đơn hàng đã được hủy thành công');
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            alert('Có lỗi xảy ra khi hủy đơn hàng');
            console.error('Error:', error);
        });
    }
}

// Animation for cards when loading
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.order-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
});
