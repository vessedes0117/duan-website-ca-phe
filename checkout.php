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

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

// 2. XỬ LÝ ĐẶT HÀNG
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    $payment_method = $_POST['payment_method'];
    
    // --- BƯỚC KIỂM TRA KHO HÀNG ---
    $total_money = 0;
    $cart_items = [];
    $error_message = "";

    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $ids_str = implode(',', $ids);
        $sql_cart = "SELECT id, name, price, stock_quantity FROM products WHERE id IN ($ids_str)";
        $res_cart = $conn->query($sql_cart);
        
        while ($row = $res_cart->fetch_assoc()) {
            $cart_qty = $_SESSION['cart'][$row['id']];
            
            // Kiểm tra tồn kho
if ($cart_qty > $row['stock_quantity']) {
    if ($row['stock_quantity'] == 0) {
        // Trường hợp hết sạch hàng
        $error_message = "Sản phẩm <strong>" . $row['name'] . "</strong> đã hết hàng.";
    } else {
        // Trường hợp mua nhiều hơn kho có
        $error_message = "Sản phẩm <strong>" . $row['name'] . "</strong> chỉ còn " . $row['stock_quantity'] . " cái trong kho. Vui lòng giảm số lượng.";
    }
    break;
}

            $total_money += $row['price'] * $cart_qty;
            $cart_items[] = [
                'id' => $row['id'],
                'price' => $row['price'],
                'qty' => $cart_qty
            ];
        }
    }

    // NẾU KHÔNG CÓ LỖI TỒN KHO THÌ MỚI TẠO ĐƠN
    if (empty($error_message)) {
        // A. Lưu đơn hàng
        $sql_order = "INSERT INTO orders (user_id, fullname, phone, address, note, total_money, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        
        // --- ĐÃ SỬA LỖI Ở DÒNG DƯỚI ĐÂY (isssSDS -> issssds) ---
        // user_id(i), fullname(s), phone(s), address(s), note(s), total_money(d), payment_method(s)
        $stmt_order->bind_param("issssds", $user_id, $fullname, $phone, $address, $note, $total_money, $payment_method);
        
        if ($stmt_order->execute()) {
            $new_order_id = $conn->insert_id;
            
            // B. Lưu chi tiết & TRỪ KHO HÀNG
            $sql_detail = "INSERT INTO order_details (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)";
            $stmt_detail = $conn->prepare($sql_detail);

            // Câu lệnh trừ kho
            $sql_update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stmt_stock = $conn->prepare($sql_update_stock);
            
            foreach ($cart_items as $item) {
                // Lưu detail
                $stmt_detail->bind_param("iidi", $new_order_id, $item['id'], $item['price'], $item['qty']);
                $stmt_detail->execute();

                // Trừ kho
                $stmt_stock->bind_param("ii", $item['qty'], $item['id']);
                $stmt_stock->execute();
            }
            
            // C. Xóa giỏ hàng & Chuyển hướng
            unset($_SESSION['cart']);
            header("Location: order-success.php?id=" . $new_order_id);
            exit();
            
        } else {
            $error_message = "Lỗi hệ thống khi tạo đơn hàng: " . $stmt_order->error;
        }
    }
}

require_once 'includes/header.php';
?>

<main class="bg-light" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-coffee">Thanh Toán</h2>
            <p class="text-muted">Hoàn tất đơn hàng để thưởng thức cà phê tuyệt hảo.</p>
        </div>

        <form action="checkout.php" method="POST">
            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-coffee"><i class="bi bi-person-lines-fill me-2"></i>Thông tin giao hàng</h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if(isset($error_message) && !empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Họ và tên</label>
                                    <input type="text" name="fullname" class="form-control" required value="<?php echo htmlspecialchars($user_info['fullname']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" required placeholder="Ví dụ: 0912345678">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Địa chỉ nhận hàng</label>
                                <textarea name="address" class="form-control" rows="2" required placeholder="Số nhà, đường, phường xã..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú (Tùy chọn)</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Lời nhắn cho shipper..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-coffee"><i class="bi bi-credit-card-2-front-fill me-2"></i>Phương thức thanh toán</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-check mb-3 border rounded p-3 bg-light">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="COD" checked>
                                <label class="form-check-label w-100 fw-bold text-dark" for="pay_cod">
                                    <i class="bi bi-cash-stack me-2 text-success"></i> Thanh toán khi nhận hàng (COD)
                                    <div class="small text-muted fw-normal mt-1">Bạn chỉ phải thanh toán khi đã nhận được hàng.</div>
                                </label>
                            </div>
                            
                            <div class="form-check mb-3 border rounded p-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_banking" value="BANK">
                                <label class="form-check-label w-100 fw-bold text-dark" for="pay_banking">
                                    <i class="bi bi-qr-code-scan me-2 text-primary"></i> Chuyển khoản Ngân hàng (VietQR)
                                    <div class="small text-muted fw-normal mt-1">Quét mã QR để thanh toán nhanh chóng, chính xác.</div>
                                </label>
                            </div>

                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_momo" value="MOMO">
                                <label class="form-check-label w-100 fw-bold text-dark" for="pay_momo">
                                    <i class="bi bi-wallet2 me-2 text-danger"></i> Ví MoMo
                                    <div class="small text-muted fw-normal mt-1">Quét mã để thanh toán qua ví MoMo.</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-coffee">Đơn hàng của bạn</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="checkout-product-list mb-4">
                                <?php
                                $total = 0;
                                if (!empty($_SESSION['cart'])) {
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
                                        <img src="images/<?php echo htmlspecialchars($p['image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary"><?php echo $qty; ?></span>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="small fw-bold"><?php echo htmlspecialchars($p['name']); ?></div>
                                    </div>
                                    <div class="fw-bold text-coffee small"><?php echo number_format($subtotal); ?> đ</div>
                                </div>
                                <?php endwhile; } ?>
                            </div>

                            <div class="d-flex justify-content-between border-top pt-3 mb-4">
                                <span class="fs-5 fw-bold text-dark">Tổng cộng</span>
                                <span class="fs-4 fw-bold" style="color: var(--color-primary);"><?php echo number_format($total); ?> đ</span>
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