<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

// Lấy ID đơn hàng từ URL để hiển thị (nếu có)
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<main class="py-5" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="container text-center">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
        </div>
        
        <h1 class="fw-bold mb-3 text-coffee">Đặt Hàng Thành Công!</h1>
        <p class="lead text-muted mb-4">Cảm ơn bạn đã mua hàng tại Cà Phê Việt.</p>
        
        <?php if ($order_id > 0): ?>
            <div class="alert alert-success d-inline-block px-5 py-3 mb-4 rounded-3">
                Mã đơn hàng của bạn là: <strong class="fs-5">#<?php echo $order_id; ?></strong>
            </div>
            <p class="mb-4 text-muted">Chúng tôi sẽ liên hệ với bạn qua số điện thoại để xác nhận đơn hàng sớm nhất.</p>
        <?php endif; ?>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">Về trang chủ</a>
            <a href="products.php" class="btn btn-primary rounded-pill px-4">Tiếp tục mua sắm</a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>