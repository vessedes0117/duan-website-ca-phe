<?php
require_once 'includes/db_connection.php'; // Kết nối CSDL
require_once 'includes/header.php';      // Nhúng header
?>

<main>
<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container">
        <h1>Tất Cả Sản Phẩm</h1>
        <p>Khám phá bộ sưu tập cà phê Việt Nam chất lượng cao với nhiều loại và hương vị đa dạng.</p>
    </div>
</section>

    <!-- PRODUCTS GRID -->
    <section class="section-light">
        <div class="container">
            <div class="row">
                <?php
                // (Toàn bộ logic PHP và vòng lặp sản phẩm giữ nguyên như cũ)
                $sql = "SELECT * FROM products ORDER BY id DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($product = $result->fetch_assoc()) {
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
                                            <?php if($i < $product['rating']): ?>★<?php else: ?>☆<?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="price-group">
                                        <span class="current-price"><?php echo number_format($product['price']); ?> đ</span>
                                        <?php if ($discount_percent > 0): ?>
                                            <span class="original-price"><?php echo number_format($product['original_price']); ?> đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="#" class="btn btn-primary">Thêm vào giỏ</a>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p class='text-center fs-4'>Hiện chưa có sản phẩm nào.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>
</main>

<?php
require_once 'includes/footer.php'; // Nhúng footer
?>