<?php
session_start();
require_once 'includes/db_connection.php';

// --- XỬ LÝ LOGIC GIỎ HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. THÊM SẢN PHẨM VÀO GIỎ (UPDATE: Chấp nhận cả add_to_cart và buy_now)
    if (isset($_POST['add_to_cart']) || isset($_POST['buy_now'])) {
        $p_id = intval($_POST['product_id']);
        $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if ($qty < 1) $qty = 1;

        if (isset($_SESSION['cart'][$p_id])) {
            $_SESSION['cart'][$p_id] += $qty;
        } else {
            $_SESSION['cart'][$p_id] = $qty;
        }
        
        // Chuyển hướng về trang giỏ hàng để người dùng kiểm tra lại trước khi thanh toán
        header("Location: cart.php");
        exit();
    }

    // 2. CẬP NHẬT SỐ LƯỢNG (Chạy khi người dùng đổi số trong ô input)
    if (isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $product_id => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $qty;
            }
        }
    }

    // 3. XÓA TỪNG SẢN PHẨM
    if (isset($_POST['remove_item'])) {
        $remove_id = intval($_POST['remove_item']);
        unset($_SESSION['cart'][$remove_id]);
    }

    // 4. XÓA HẾT GIỎ HÀNG
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
    }
    
    // Refresh lại trang để cập nhật số liệu
    header("Location: cart.php");
    exit();
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<main>
    <section class="page-header">
        <div class="container">
            <h1>Giỏ Hàng Của Bạn</h1>
            <p>Kiểm tra lại các sản phẩm và tiến hành thanh toán.</p>
        </div>
    </section>

    <section class="section-light">
        <div class="container">
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <div class="fs-1 text-muted mb-3"><i class="bi bi-cart-x"></i></div>
                    <h3>Giỏ hàng đang trống!</h3>
                    <p class="text-muted">Hãy dạo một vòng và chọn cho mình hương vị cà phê yêu thích nhé.</p>
                    <a href="products.php" class="btn btn-primary btn-lg mt-3">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <form action="cart.php" method="POST">
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="cart-container table-responsive">
                                <table class="table cart-table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Sản phẩm</th>
                                            <th scope="col">Giá</th>
                                            <th scope="col" style="width: 15%;">Số lượng</th>
                                            <th scope="col">Tạm tính</th>
                                            <th scope="col" class="text-center">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Lấy danh sách ID sản phẩm
                                        $cart_ids = array_keys($_SESSION['cart']);
                                        $ids_string = implode(',', $cart_ids);
                                        
                                        // Truy vấn CSDL
                                        $sql = "SELECT * FROM products WHERE id IN ($ids_string)";
                                        $result = $conn->query($sql);
                                        
                                        $total_money = 0;

                                        if ($result):
                                            while ($product = $result->fetch_assoc()):
                                                $p_id = $product['id'];
                                                $qty = $_SESSION['cart'][$p_id];
                                                $line_total = $product['price'] * $qty;
                                                $total_money += $line_total;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" class="cart-product-img me-3" alt="">
                                                    <div class="cart-product-name">
                                                        <a href="product-detail.php?id=<?php echo $p_id; ?>">
                                                            <?php echo htmlspecialchars($product['name']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="fw-bold text-muted">
                                                <?php echo number_format($product['price']); ?> đ
                                            </td>
                                            
                                            <td>
                                                <input type="number" name="qty[<?php echo $p_id; ?>]" 
                                                       value="<?php echo $qty; ?>" 
                                                       min="1" class="form-control text-center fw-bold"
                                                       onchange="this.form.submit()"> 
                                            </td>
                                            
                                            <td class="fw-bold text-coffee">
                                                <?php echo number_format($line_total); ?> đ
                                            </td>
                                            
                                            <td class="text-center">
                                                <button type="submit" name="remove_item" value="<?php echo $p_id; ?>" 
                                                        class="btn btn-link text-danger p-2"
                                                        onclick="return confirm('Xóa sản phẩm này?');"
                                                        title="Xóa sản phẩm">
                                                    <i class="bi bi-trash-fill fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile; 
                                        endif;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <a href="products.php" class="btn btn-outline-secondary rounded-pill">
                                    <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                                </a>
                                
                                <button type="submit" name="clear_cart" class="btn btn-outline-danger rounded-pill"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?');">
                                    <i class="bi bi-x-circle"></i> Xóa hết
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="cart-summary shadow-sm">
                                <h5 class="fw-bold mb-3 font-display text-uppercase">ĐƠN HÀNG</h5>
                                
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span><?php echo number_format($total_money); ?> đ</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí vận chuyển:</span>
                                    <span class="text-success">Miễn phí</span>
                                </div>
                                <div class="summary-total">
                                    <span>Tổng cộng:</span>
                                    <span class="total-price"><?php echo number_format($total_money); ?> đ</span>
                                </div>
                                <div class="mt-4">
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="checkout.php" class="btn btn-primary w-100 py-3 fw-bold rounded-pill text-uppercase shadow">
                                            Tiến hành thanh toán
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-secondary w-100 py-3 fw-bold rounded-pill mb-2">
                                            Đăng nhập để thanh toán
                                        </a>
                                        <div class="text-center small text-muted">Hoặc <a href="register.php">đăng ký tài khoản mới</a></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>