<?php
session_start();
require_once 'includes/db_connection.php';

// 1. KIỂM TRA ĐĂNG NHẬP & GIỎ HÀNG
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: products.php");
    exit();
}

// Lấy thông tin người dùng để điền sẵn vào form
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

// 2. XỬ LÝ ĐẶT HÀNG (KHI BẤM NÚT "ĐẶT HÀNG")
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    
    // Nhận dữ liệu từ form
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    
    // Tính lại tổng tiền lần cuối cho chính xác
    $total_money = 0;
    $cart_items = []; // Mảng tạm để lưu thông tin chi tiết
    
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $ids_str = implode(',', $ids);
        $sql_cart = "SELECT id, price FROM products WHERE id IN ($ids_str)";
        $res_cart = $conn->query($sql_cart);
        while ($row = $res_cart->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $total_money += $row['price'] * $qty;
            // Lưu vào mảng tạm để lát dùng insert order_details
            $cart_items[] = [
                'id' => $row['id'],
                'price' => $row['price'],
                'qty' => $qty
            ];
        }
    }

    // A. LƯU VÀO BẢNG `orders`
    $sql_order = "INSERT INTO orders (user_id, fullname, phone, address, note, total_money) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("issssd", $user_id, $fullname, $phone, $address, $note, $total_money);
    
    if ($stmt_order->execute()) {
        $new_order_id = $conn->insert_id; // Lấy ID của đơn hàng vừa tạo
        
        // B. LƯU VÀO BẢNG `order_details`
        $sql_detail = "INSERT INTO order_details (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);
        
        foreach ($cart_items as $item) {
            $stmt_detail->bind_param("iidi", $new_order_id, $item['id'], $item['price'], $item['qty']);
            $stmt_detail->execute();
        }
        
        // C. XÓA GIỎ HÀNG & CHUYỂN HƯỚNG
        unset($_SESSION['cart']);
        header("Location: order-success.php?id=" . $new_order_id);
        exit();
        
    } else {
        $error_message = "Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại.";
    }
}

require_once 'includes/header.php';
?>

<main class="bg-light" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-coffee">Thanh Toán</h2>
            <p class="text-muted">Vui lòng kiểm tra thông tin giao hàng và đơn hàng.</p>
        </div>

        <form action="checkout.php" method="POST">
            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-coffee"><i class="bi bi-geo-alt-fill me-2"></i>Thông tin nhận hàng</h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if(isset($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên người nhận</label>
                                <input type="text" name="fullname" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user_info['fullname']); ?>" placeholder="Nhập họ tên">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" required placeholder="Nhập số điện thoại liên hệ">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Địa chỉ giao hàng</label>
                                <textarea name="address" class="form-control" rows="2" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú đơn hàng (Tùy chọn)</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-coffee"><i class="bi bi-bag-check-fill me-2"></i>Đơn hàng của bạn</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="checkout-product-list mb-4">
                                <?php
                                $total = 0;
                                // Lấy lại thông tin sản phẩm để hiển thị
                                $ids = array_keys($_SESSION['cart']);
                                $ids_str = implode(',', $ids);
                                $sql = "SELECT * FROM products WHERE id IN ($ids_str)";
                                $result = $conn->query($sql);
                                
                                while($p = $result->fetch_assoc()):
                                    $qty = $_SESSION['cart'][$p['id']];
                                    $subtotal = $p['price'] * $qty;
                                    $total += $subtotal;
                                ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="position-relative">
                                        <img src="images/<?php echo htmlspecialchars($p['image']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" alt="">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                            <?php echo $qty; ?>
                                        </span>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-0 text-dark small fw-bold"><?php echo htmlspecialchars($p['name']); ?></h6>
                                    </div>
                                    <div class="fw-bold text-coffee small">
                                        <?php echo number_format($subtotal); ?> đ
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính</span>
                                <span class="fw-bold"><?php echo number_format($total); ?> đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Phí vận chuyển</span>
                                <span class="text-success">Miễn phí</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-3 mb-4">
                                <span class="fs-5 fw-bold text-dark">Tổng cộng</span>
                                <span class="fs-4 fw-bold" style="color: var(--color-primary);"><?php echo number_format($total); ?> đ</span>
                            </div>

                            <div class="alert alert-warning small mb-3">
                                <i class="bi bi-cash-coin me-1"></i> Phương thức thanh toán: <strong>Thanh toán khi nhận hàng (COD)</strong>
                            </div>

                            <button type="submit" name="place_order" class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase shadow-sm">
                                Đặt Hàng Ngay
                            </button>
                            
                            <div class="text-center mt-3">
                                <a href="cart.php" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Quay lại giỏ hàng</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>