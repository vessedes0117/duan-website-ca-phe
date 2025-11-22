<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

// 1. LẤY ID VÀ KIỂM TRA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'products.php';</script>";
    exit();
}
$product_id = intval($_GET['id']);

// 2. XỬ LÝ THÊM VÀO GIỎ HÀNG (LOGIC PHP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $qty = intval($_POST['quantity']);
    if ($qty < 1) $qty = 1;

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm hoặc cập nhật số lượng
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    // Hiển thị thông báo thành công bằng JS
    echo "<script>
        alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
        window.location.href = 'product-detail.php?id=$product_id';
    </script>";
}

// 3. XỬ LÝ GỬI BÌNH LUẬN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Bạn cần đăng nhập để đánh giá!'); window.location.href = 'login.php';</script>";
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = intval($_POST['rating']);
        $content = trim($_POST['content']);
        
        $stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, rating, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $content);
        $stmt->execute();
        echo "<script>alert('Cảm ơn bạn đã đánh giá!'); window.location.href = 'product-detail.php?id=$product_id';</script>";
    }
}

// 4. LẤY THÔNG TIN SẢN PHẨM
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) die("Sản phẩm không tồn tại.");

// 5. TÍNH TOÁN THỐNG KÊ SAO (CHO UX REVIEW)
$sql_stats = "SELECT rating, COUNT(*) as count FROM comments WHERE product_id = $product_id GROUP BY rating";
$result_stats = $conn->query($sql_stats);
$star_counts = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
$total_reviews = 0;
$total_score = 0;

while($row = $result_stats->fetch_assoc()) {
    $star_counts[$row['rating']] = $row['count'];
    $total_reviews += $row['count'];
    $total_score += ($row['rating'] * $row['count']);
}
$avg_rating = $total_reviews > 0 ? round($total_score / $total_reviews, 1) : 5.0;
?>

<main class="container py-5">
    <!-- KHU VỰC TRÊN: ẢNH VÀ THÔNG TIN MUA HÀNG -->
    <div class="product-detail-container">
        <div class="row">
            <!-- CỘT TRÁI: GALLERY ẢNH -->
            <div class="col-md-6">
                <!-- Ảnh chính -->
                <div class="main-image-container">
                    <img id="mainImg" src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="Ảnh sản phẩm">
                </div>
                <!-- Thumbnails (Giả lập bằng cách lặp lại ảnh chính) -->
                <div class="thumbnail-container mt-3">
                    <img class="thumbnail active" src="images/<?php echo htmlspecialchars($product['image']); ?>" onclick="changeImage(this)">
                    <!-- Giả lập thêm vài ảnh khác (bạn có thể thay bằng ảnh thật nếu có) -->
                    <img class="thumbnail" src="https://images.pexels.com/photos/1695052/pexels-photo-1695052.jpeg?auto=compress&cs=tinysrgb&w=200" onclick="changeImage(this)">
                    <img class="thumbnail" src="https://images.pexels.com/photos/302899/pexels-photo-302899.jpeg?auto=compress&cs=tinysrgb&w=200" onclick="changeImage(this)">
                    <img class="thumbnail" src="https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&cs=tinysrgb&w=200" onclick="changeImage(this)">
                </div>
            </div>

            <!-- CỘT PHẢI: THÔNG TIN & NÚT MUA -->
            <div class="col-md-6 mt-4 mt-md-0">
                <div class="d-flex justify-content-between align-items-start">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if($product['original_price'] > $product['price']): ?>
                        <span class="badge bg-danger rounded-pill">-<?php echo round((($product['original_price'] - $product['price'])/$product['original_price'])*100); ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <span class="fs-5 fw-bold"><?php echo $avg_rating; ?></span> ★★★★★
                    </div>
                    <span class="text-muted">(<?php echo $total_reviews; ?> đánh giá)</span>
                    <span class="text-muted mx-2">|</span>
                    <span class="text-success">Đã bán <?php echo rand(100, 999); ?>+</span>
                </div>

                <div class="price-group mb-4">
                    <span class="product-price-large"><?php echo number_format($product['price']); ?> ₫</span>
                    <?php if($product['original_price'] > 0): ?>
                        <span class="original-price fs-5"><?php echo number_format($product['original_price']); ?> ₫</span>
                    <?php endif; ?>
                </div>

                <div class="product-meta">
                    <p><i class="bi bi-check-circle-fill text-success"></i> Hàng chính hãng 100%</p>
                    <p><i class="bi bi-truck text-primary"></i> Miễn phí vận chuyển cho đơn từ 500k</p>
                </div>

                <!-- FORM MUA HÀNG -->
                <form method="POST" class="d-flex gap-3 align-items-center mt-4">
                    <div class="input-group" style="width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                        <input type="number" name="quantity" id="qtyInput" class="form-control text-center" value="1" min="1">
                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bi bi-cart-plus-fill"></i> Thêm vào giỏ hàng
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KHU VỰC DƯỚI: TAB THÔNG TIN & ĐÁNH GIÁ -->
    <div class="product-detail-container">
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button">Thông tin sản phẩm</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button">Đánh giá (<?php echo $total_reviews; ?>)</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- TAB 1: MÔ TẢ -->
            <div class="tab-pane fade show active" id="desc">
                <h4>Mô tả chi tiết</h4>
                <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <hr>
                <p><strong>Xuất xứ:</strong> Việt Nam</p>
                <p><strong>Thương hiệu:</strong> Cà Phê Việt</p>
                <p><strong>Hạn sử dụng:</strong> 12 tháng kể từ ngày sản xuất</p>
                <p><strong>Hướng dẫn bảo quản:</strong> Để nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp.</p>
            </div>

            <!-- TAB 2: ĐÁNH GIÁ (DESIGN CHUẨN E-COM) -->
            <div class="tab-pane fade" id="review">
                <div class="row">
                    <!-- Cột Trái: Tổng quan -->
                    <div class="col-md-4 mb-4">
                        <div class="review-summary">
                            <div class="average-rating"><?php echo $avg_rating; ?>/5</div>
                            <div class="text-warning mb-2">★★★★★</div>
                            <div class="text-muted mb-3"><?php echo $total_reviews; ?> nhận xét</div>
                            
                            <!-- Progress Bars -->
                            <?php for($i=5; $i>=1; $i--): 
                                $percent = $total_reviews > 0 ? ($star_counts[$i] / $total_reviews) * 100 : 0;
                            ?>
                            <div class="review-row">
                                <span class="me-2"><?php echo $i; ?> sao</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <span class="ms-2 text-muted small"><?php echo $star_counts[$i]; ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Form Viết đánh giá -->
                        <div class="mt-4">
                            <h5>Gửi đánh giá của bạn</h5>
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST">
                                <div class="mb-2">
                                    <select name="rating" class="form-select">
                                        <option value="5">★★★★★ (Tuyệt vời)</option>
                                        <option value="4">★★★★ (Tốt)</option>
                                        <option value="3">★★★ (Bình thường)</option>
                                        <option value="2">★★ (Kém)</option>
                                        <option value="1">★ (Tệ)</option>
                                    </select>
                                </div>
                                <textarea name="content" class="form-control mb-2" rows="3" placeholder="Nhập nhận xét..."></textarea>
                                <button type="submit" name="submit_review" class="btn btn-primary w-100">Gửi đánh giá</button>
                            </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary w-100">Đăng nhập để đánh giá</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Cột Phải: Danh sách bình luận -->
                    <div class="col-md-8">
                        <?php
                        $stmt_cmt = $conn->prepare("SELECT c.*, u.fullname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = ? ORDER BY c.created_at DESC");
                        $stmt_cmt->bind_param("i", $product_id);
                        $stmt_cmt->execute();
                        $result_cmt = $stmt_cmt->get_result();

                        if ($result_cmt->num_rows > 0):
                            while ($cmt = $result_cmt->fetch_assoc()):
                        ?>
                            <div class="user-review">
                                <div class="d-flex">
                                    <div class="user-avatar"><?php echo substr($cmt['fullname'], 0, 1); ?></div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($cmt['fullname']); ?></div>
                                        <div class="text-warning small">
                                            <?php for($k=0; $k<5; $k++) echo ($k < $cmt['rating']) ? '★' : '☆'; ?>
                                        </div>
                                        <div class="text-muted small mb-2"><?php echo date('d/m/Y H:i', strtotime($cmt['created_at'])); ?> | Phân loại: Túi 500g</div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($cmt['content'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; else: ?>
                            <p class="text-center text-muted py-5">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JAVASCRIPT CHO TRANG CHI TIẾT -->
<script>
    // Hàm đổi ảnh khi click thumbnail
    function changeImage(element) {
        document.getElementById('mainImg').src = element.src;
        // Xóa class active cũ
        document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
        // Thêm class active mới
        element.classList.add('active');
    }

    // Hàm tăng giảm số lượng
    function increaseQty() {
        var input = document.getElementById('qtyInput');
        input.value = parseInt(input.value) + 1;
    }

    function decreaseQty() {
        var input = document.getElementById('qtyInput');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>