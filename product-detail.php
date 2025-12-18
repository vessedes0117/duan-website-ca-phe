<?php
// --- PHẦN XỬ LÝ LOGIC ---
session_start(); 
require_once 'includes/db_connection.php';

// 1. KIỂM TRA ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}
$product_id = intval($_GET['id']);

// 2. XỬ LÝ POST (PRG Pattern)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Gửi đánh giá (Logic đánh giá giữ nguyên tại đây)
    if (isset($_POST['submit_review'])) {
        if (!isset($_SESSION['user_id'])) {
            echo "<script>alert('Vui lòng đăng nhập!'); window.location.href = 'login.php';</script>";
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $rating = intval($_POST['rating']); // Lấy từ input hidden
        $content = trim($_POST['content']);

        // Validate cơ bản
        if ($rating < 1 || $rating > 5) $rating = 5; // Mặc định 5 nếu lỗi

        if (!empty($content)) {
            $stmt_review = $conn->prepare("INSERT INTO comments (product_id, user_id, rating, content) VALUES (?, ?, ?, ?)");
            $stmt_review->bind_param("iiis", $product_id, $user_id, $rating, $content);
            $stmt_review->execute();
            $stmt_review->close();
        }
        header("Location: product-detail.php?id=$product_id#review");
        exit(); 
    }
}

require_once 'includes/header.php'; 

// 3. LẤY DỮ LIỆU
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) die("Sản phẩm không tồn tại.");

// Xử lý ảnh
$image_list = [];
if (!empty($product['image'])) $image_list[] = $product['image'];
if (!empty($product['gallery'])) {
    $gallery_files = explode(',', $product['gallery']);
    foreach ($gallery_files as $file) if (!empty(trim($file))) $image_list[] = trim($file);
}
$image_list = array_unique($image_list);
if (empty($image_list)) $image_list[] = 'default.jpg';

// Thống kê sao
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
$average_rating = $total_reviews > 0 ? round($total_score / $total_reviews, 1) : 5.0;

$sql_comments = "SELECT c.*, u.fullname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = $product_id ORDER BY c.created_at DESC";
$result_comments = $conn->query($sql_comments);
?>

<main class="container py-5">
    <!-- PHẦN TRÊN: THÔNG TIN SẢN PHẨM -->
    <div class="product-detail-container">
        <div class="row">
            <div class="col-md-6">
                <div class="main-image-container">
                    <img id="mainImg" src="images/<?php echo htmlspecialchars($image_list[0]); ?>" alt="Ảnh sản phẩm">
                </div>
                <?php if (count($image_list) > 1): ?>
                <div class="thumbnail-container">
                    <?php foreach($image_list as $index => $img_file): ?>
                        <img class="thumbnail <?php echo ($index === 0) ? 'active' : ''; ?>" 
                             src="images/<?php echo htmlspecialchars($img_file); ?>" onclick="changeImage(this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mt-4 mt-md-0">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="mb-3 d-flex align-items-center">
                     <span class="text-warning fs-5 me-2">
                        <?php echo $average_rating; ?> 
                        <?php for($i=1; $i<=5; $i++) echo ($i <= round($average_rating)) ? '★' : '☆'; ?>
                     </span> 
                     <span class="text-muted border-start ps-2"><?php echo $total_reviews; ?> Đánh giá</span>
                </div>

                <div class="price-group mb-4">
                    <span class="product-price-large"><?php echo number_format($product['price']); ?> ₫</span>
                    <?php if($product['original_price'] > $product['price']): ?>
                        <span class="original-price fs-5 text-muted text-decoration-line-through ms-3">
                            <?php echo number_format($product['original_price']); ?> ₫
                        </span>
                        <span class="badge bg-danger ms-2">-<?php echo round((($product['original_price'] - $product['price'])/$product['original_price'])*100); ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="product-meta mb-4">
                    <?php if ($product['stock_quantity'] > 0): ?>
    <p class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Tình trạng: <strong>Còn hàng</strong> (<?php echo $product['stock_quantity']; ?>)</p>
<?php else: ?>
    <p class="mb-2"><i class="bi bi-x-circle-fill text-danger"></i> Tình trạng: <strong class="text-danger">Hết hàng</strong></p>
<?php endif; ?>
                    <p class="mb-2"><i class="bi bi-truck text-coffee"></i> Vận chuyển: <strong>Miễn phí cho đơn từ 500k</strong></p>
                </div>
                
                <p class="text-muted mb-4"><?php echo mb_strimwidth(strip_tags($product['description']), 0, 150, "..."); ?></p>

                <!-- CẬP NHẬT Ở ĐÂY: Form đã được sửa ID và Type của nút bấm -->
                <?php if ($product['stock_quantity'] > 0): ?>
    <form action="cart.php" method="POST" id="productForm" class="d-flex flex-column flex-md-row gap-3">
        <input type="hidden" name="add_to_cart" value="1">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

        <div class="input-group" style="width: 140px;">
            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
            <input type="number" name="quantity" id="qtyInput" class="form-control text-center fw-bold" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
        </div>

        <div class="d-flex gap-2 flex-grow-1">
            <button type="button" id="btnAddToCart" class="btn btn-outline-primary flex-grow-1">
                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
            </button>
            <button type="submit" name="buy_now" value="1" class="btn btn-primary flex-grow-1 fw-bold">
                Thanh toán
            </button>
        </div>
    </form>
<?php else: ?>
    <div class="d-grid gap-2">
        <button class="btn btn-secondary py-3 fw-bold disabled" type="button" disabled>
            <i class="bi bi-emoji-frown"></i> Tạm hết hàng
        </button>
        <small class="text-muted text-center">Sản phẩm đang được nhập thêm. Vui lòng quay lại sau.</small>
    </div>
<?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PHẦN DƯỚI: TAB MÔ TẢ & ĐÁNH GIÁ -->
    <div class="product-detail-container mt-4">
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Mô tả chi tiết</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#review">Đánh giá (<?php echo $total_reviews; ?>)</button></li>
        </ul>

        <div class="tab-content">
            <!-- TAB MÔ TẢ (GIỮ NGUYÊN) -->
            <div class="tab-pane fade show active" id="desc">
                <h5 class="text-coffee border-start border-4 border-coffee ps-2 fw-bold">Chi tiết sản phẩm</h5>
                <div class="content-text mb-5 mt-3">
                    <?php echo !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : "<p class='text-muted'>Đang cập nhật...</p>"; ?>
                </div>
                <div class="row bg-light p-4 rounded-3">
                    <div class="col-md-6">
                        <h5 class="text-coffee fw-bold mb-3">Thông số sản phẩm</h5>
                        <table class="table table-borderless m-0">
                            <tbody>
                                <tr><td class="text-muted" width="35%">Thương hiệu:</td><td class="fw-bold">Cà Phê Việt</td></tr>
                                <tr><td class="text-muted">Xuất xứ:</td><td>Việt Nam</td></tr>
                                <tr><td class="text-muted">Loại:</td><td><?php echo htmlspecialchars($product['category']); ?></td></tr>
                                <tr><td class="text-muted">Hạn sử dụng:</td><td>12 tháng</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-coffee fw-bold mb-3">Hướng dẫn sử dụng</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check2-circle text-coffee"></i> <strong>Pha phin:</strong> Tráng phin, cho 20g cà phê, ủ 20ml nước sôi.</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-coffee"></i> <strong>Pha máy:</strong> Nén lực vừa đủ, chiết xuất 25-30ml espresso.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB ĐÁNH GIÁ (UPDATED UI/UX) -->
            <div class="tab-pane fade" id="review">
                <!-- 1. DASHBOARD THỐNG KÊ -->
                <div class="review-dashboard">
                    <div class="row align-items-center">
                        <!-- Điểm số -->
                        <div class="col-md-4 border-end d-flex flex-column align-items-center justify-content-center">
                            <div class="text-muted mb-1 fw-bold">Đánh giá trung bình</div>
                            <div class="score-num" style="font-size: 4rem;"><?php echo $average_rating; ?></div>
                            <div class="text-warning fs-4 mb-2">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= round($average_rating)) ? '★' : '☆'; ?>
                            </div>
                            <div class="text-muted small"><?php echo $total_reviews; ?> nhận xét</div>
                        </div>
                        <!-- Thanh Progress Bar (Đã fix layout) -->
                        <div class="col-md-8 ps-md-5 mt-3 mt-md-0">
                            <?php for($star=5; $star>=1; $star--): 
                                $percent = $total_reviews > 0 ? ($star_counts[$star] / $total_reviews) * 100 : 0;
                            ?>
                            <div class="rating-row">
                                <div class="rating-label"><?php echo $star; ?> sao</div>
                                <div class="progress progress-container">
                                    <div class="progress-bar progress-bar-coffee" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <div class="rating-count"><?php echo $star_counts[$star]; ?></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <!-- 2. FORM ĐÁNH GIÁ (UPDATED UI) -->
                    <div class="col-lg-4 mb-5">
                        <div class="review-form-box sticky-top" style="top: 90px; z-index: 1;">
                            <h5 class="fw-bold mb-4">Viết đánh giá của bạn</h5>
                            
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <form method="POST" id="reviewForm">
                                    <input type="hidden" name="submit_review" value="1">
                                    <!-- Chọn sao bằng Click -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">1. Chọn mức độ hài lòng</label>
                                        <div class="star-rating-group" id="starRatingGroup">
                                            <i class="bi bi-star-fill star-icon" data-value="1"></i>
                                            <i class="bi bi-star-fill star-icon" data-value="2"></i>
                                            <i class="bi bi-star-fill star-icon" data-value="3"></i>
                                            <i class="bi bi-star-fill star-icon" data-value="4"></i>
                                            <i class="bi bi-star-fill star-icon" data-value="5"></i>
                                        </div>
                                        <!-- Input ẩn để chứa giá trị sao gửi đi -->
                                        <input type="hidden" name="rating" id="ratingInput" value="5">
                                        <div class="mt-2 small text-coffee fw-bold" id="ratingText">Tuyệt vời</div>
                                    </div>

                                    <!-- Textarea hiện đại -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">2. Nội dung đánh giá</label>
                                        <textarea name="content" class="form-control modern-textarea" rows="5" placeholder="Chất lượng sản phẩm thế nào? Giao hàng có nhanh không?..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">Gửi đánh giá ngay</button>
                                </form>
                            <?php else: ?>
                                <div class="text-center py-4 bg-light rounded-3 border border-dashed">
                                    <p class="text-muted mb-3">Bạn cần đăng nhập để đánh giá sản phẩm này.</p>
                                    <a href="login.php" class="btn btn-outline-primary px-4 rounded-pill">Đăng nhập / Đăng ký</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. DANH SÁCH REVIEW -->
                    <div class="col-lg-8 ps-lg-5">
                        <h5 class="mb-4 fw-bold">Khách hàng nhận xét (<?php echo $total_reviews; ?>)</h5>
                        <?php if($result_comments->num_rows > 0): ?>
                            <?php while($cmt = $result_comments->fetch_assoc()): ?>
                                <div class="review-item">
                                    <div class="d-flex">
                                        <div class="user-avatar-circle shadow-sm">
                                            <?php echo strtoupper(substr($cmt['fullname'], 0, 1)); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($cmt['fullname']); ?></div>
                                                <div class="review-date text-muted small"><?php echo date('d/m/Y', strtotime($cmt['created_at'])); ?></div>
                                            </div>
                                            <div class="text-warning small mb-2">
                                                <?php for($k=1; $k<=5; $k++) echo ($k <= $cmt['rating']) ? '★' : '☆'; ?>
                                                <span class="text-success ms-2 fst-italic fw-normal bg-light px-2 py-1 rounded border" style="font-size: 0.75rem;"><i class="bi bi-check-circle-fill"></i> Đã mua hàng</span>
                                            </div>
                                            <div class="review-content">
                                                <?php echo nl2br(htmlspecialchars($cmt['content'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 bg-light rounded-3">
                                <i class="bi bi-chat-square-quote fs-1 text-muted mb-3"></i>
                                <p class="text-muted">Chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ cảm nhận!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</main>

<!-- JAVASCRIPT XỬ LÝ -->
<script>
    // 1. Logic chọn sao (Star Rating)
    const stars = document.querySelectorAll('.star-icon');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    const ratingLabels = {
        1: "Tệ",
        2: "Không hài lòng",
        3: "Bình thường",
        4: "Hài lòng",
        5: "Tuyệt vời"
    };

    if(stars.length > 0) {
        // Mặc định chọn 5 sao
        highlightStars(5);

        stars.forEach(star => {
            // Xử lý khi click
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingInput.value = value;
                highlightStars(value);
                ratingText.innerText = ratingLabels[value];
            });

            // Xử lý khi hover
            star.addEventListener('mouseover', function() {
                const value = this.getAttribute('data-value');
                highlightStars(value);
            });
        });

        // Khi chuột rời khỏi vùng sao, quay lại giá trị đã chọn
        document.getElementById('starRatingGroup').addEventListener('mouseleave', function() {
            highlightStars(ratingInput.value);
        });
    }

    function highlightStars(count) {
        stars.forEach(star => {
            const value = star.getAttribute('data-value');
            if(value <= count) {
                star.classList.add('selected');
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill');
            } else {
                star.classList.remove('selected');
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star'); // Chuyển thành sao rỗng
            }
        });
    }

    // 2. Các script cũ
    function changeImage(el) {
        document.getElementById('mainImg').src = el.src;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
    function increaseQty() { document.getElementById('qtyInput').value++; }
    function decreaseQty() { 
        var el = document.getElementById('qtyInput'); 
        if(el.value > 1) el.value--; 
    }
</script>

<!-- THÊM ICON BOOTSTRAP NẾU CHƯA CÓ (Thường đã có trong header) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<?php require_once 'includes/footer.php'; ?>