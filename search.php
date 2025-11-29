<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

// Lấy từ khóa tìm kiếm từ URL và đảm bảo an toàn
$search_query = "";
if (isset($_GET['keyword'])) {
    $search_query = trim($_GET['keyword']);
}
?>

<main>
    <!-- PAGE HEADER -->
    <section class="page-header">
        <div class="container">
            <h1>Kết Quả Tìm Kiếm</h1>
            <?php if (!empty($search_query)): ?>
                <p>Cho từ khóa: "<?php echo htmlspecialchars($search_query); ?>"</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- PRODUCTS GRID -->
    <section class="section-light">
        <div class="container">
            <div class="row">
                <?php
                if (!empty($search_query)) {
                    // 1. Chuẩn bị câu lệnh SQL an toàn với Prepared Statements
                    $sql = "SELECT * FROM products WHERE name LIKE ?";
                    $stmt = $conn->prepare($sql);
                    
                    // 2. Thêm ký tự '%' vào từ khóa và bind param
                    $search_term_like = "%" . $search_query . "%";
                    $stmt->bind_param("s", $search_term_like);
                    
                    // 3. Thực thi câu lệnh
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while($product = $result->fetch_assoc()) {
                            // Tính toán % giảm giá
                            $discount_percent = 0;
                            if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
                                $discount_percent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                            }
                ?>
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="product-card">
                                    <div class="product-card-img-container">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                            <img src="images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </a>
                                        <?php if ($discount_percent > 0): ?>
                                            <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                        <div class="rating-stars">
                                            <?php for($i = 0; $i < 5; $i++): ?>
                                                <?php if($i < $product['rating']): ?>
                                                    ★ <!-- Ngôi sao đầy -->
                                                <?php else: ?>
                                                    ☆ <!-- Ngôi sao rỗng -->
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="price-group">
                                            <span class="current-price"><?php echo number_format($product['price']); ?> đ</span>
                                            <?php if ($discount_percent > 0): ?>
                                                <span class="original-price"><?php echo number_format($product['original_price']); ?> đ</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- CẬP NHẬT: Nút Mua ngay dẫn về trang chi tiết -->
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100 mt-auto">Mua ngay</a>
                                        
                                    </div>
                                </div>
                            </div>
                <?php
                        } // Kết thúc vòng lặp while
                    } else {
                        echo "<p class='text-center fs-4'>Không tìm thấy sản phẩm nào phù hợp.</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p class='text-center fs-4'>Vui lòng nhập từ khóa để tìm kiếm.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>
</main>
    
<?php require_once 'includes/footer.php'; ?>