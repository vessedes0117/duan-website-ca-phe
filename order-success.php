<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
}
?>

<main class="bg-light" style="padding-top: 120px; padding-bottom: 50px; min-height: 80vh;">
    <div class="container">
        <?php if ($order): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h2 class="fw-bold mb-3 text-coffee">Đặt Hàng Thành Công!</h2>
                    <p class="text-muted mb-4">Mã đơn hàng: <strong class="fs-5 text-dark">#<?php echo $order_id; ?></strong></p>

                    <?php if ($order['payment_method'] == 'BANK'): ?>
                        <div class="card shadow-sm border-primary mb-4 mx-auto" style="max-width: 500px;">
                            <div class="card-header bg-primary text-white text-center py-3">
                                <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>Quét mã để thanh toán</h5>
                            </div>
                            <div class="card-body text-center p-4">
                                <p class="small text-muted mb-3">Vui lòng mở App Ngân hàng và quét mã bên dưới để thanh toán.</p>
                                
                                <?php
                                    $bank_id = "MB"; 
                                    $account_no = "0969649620"; 
                                    $account_name = "TRAN XUAN BACH"; 
                                    $amount = $order['total_money'];
                                    $content = "THANH TOAN DON " . $order_id;
                                    
                                    $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount={$amount}&addInfo={$content}&accountName={$account_name}";
                                ?>
                                <img src="<?php echo $qr_url; ?>" alt="Mã QR Thanh Toán" class="img-fluid mb-3 border rounded" style="max-width: 300px;">
                                
                                <div class="alert alert-warning small text-start">
                                    <strong><i class="bi bi-info-circle"></i> Thông tin chuyển khoản thủ công:</strong><br>
                                    - Ngân hàng: <strong>MB Bank</strong><br>
                                    - STK: <strong>0969649620</strong><br>
                                    - Chủ TK: <strong>TRAN XUAN BACH</strong><br>
                                    - Số tiền: <strong><?php echo number_format($amount); ?> đ</strong><br>
                                    - Nội dung: <strong><?php echo $content; ?></strong>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($order['payment_method'] == 'MOMO'): ?>
                        <div class="card shadow-sm border-danger mb-4 mx-auto" style="max-width: 500px;">
                            <div class="card-header bg-danger text-white text-center py-3">
                                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Thanh toán qua MoMo</h5>
                            </div>
                            <div class="card-body text-center p-4">
                                <p class="small text-muted">Quét mã QR để chuyển tiền qua MoMo</p>
                                <img src="images/momo_qr.jpg" alt="QR MoMo" class="img-fluid mb-3 border rounded" style="max-width: 250px;">
                                <p class="fw-bold text-danger">Số tiền: <?php echo number_format($order['total_money']); ?> đ</p>
                                <p class="small text-muted">Nội dung chuyển tiền: <strong>TT DON <?php echo $order_id; ?></strong></p>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-info d-inline-block px-5 py-3 rounded-3 shadow-sm">
                            Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng sớm nhất.<br>
                            Bạn vui lòng chuẩn bị tiền mặt: <strong><?php echo number_format($order['total_money']); ?> đ</strong> khi nhận hàng.
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">Về trang chủ</a>
                        <a href="products.php" class="btn btn-primary rounded-pill px-4">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center pt-5">
                <h3>Không tìm thấy đơn hàng!</h3>
                <a href="index.php" class="btn btn-primary mt-3">Về trang chủ</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>