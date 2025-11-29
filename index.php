<?php require_once 'includes/header.php'; ?>

<main>
    <!-- HERO BANNER (GIỮ NGUYÊN) -->
    <section class="hero-banner text-center d-flex align-items-center">
        <div class="container">
            <h1 class="display-4">Cà Phê Việt Nam</h1>
            <p class="lead">Hương vị đậm đà, truyền thống thuần túy</p>
            <a href="products.php" class="btn btn-primary btn-lg mx-2">Khám phá sản phẩm</a>
        </div>
    </section>

    <!-- SECTION SẢN PHẨM NỔI BẬT (ĐÃ SỬA ĐỂ CÓ LINK CHÍNH XÁC) -->
    <section id="featured-products" class="section-light">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <p class="section-subtitle">Những sản phẩm cà phê chất lượng cao, được chọn lọc kỹ càng từ vùng trồng cà phê nổi tiếng của Việt Nam.</p>
            </div>

            <div class="row">
                <?php
                // Kết nối CSDL để lấy sản phẩm thực tế
                require_once 'includes/db_connection.php';
                
                // Lấy 4 sản phẩm mới nhất để hiển thị
                $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 4";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($product = $result->fetch_assoc()) {
                        // TẠO LINK ĐẾN TRANG CHI TIẾT DỰA TRÊN ID SẢN PHẨM
                        $product_link = "product-detail.php?id=" . $product['id'];

                        // Tính toán % giảm giá
                        $discount_percent = 0;
                        if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
                            $discount_percent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                        }
                ?>
                        <!-- Thẻ Sản Phẩm -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="product-card h-100 d-flex flex-column">
                                <div class="product-card-img-container">
                                    <!-- Link bao quanh ảnh -->
                                    <a href="<?php echo $product_link; ?>">
                                        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                    <?php if ($discount_percent > 0): ?>
                                        <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <!-- Link bao quanh tên sản phẩm -->
                                        <a href="<?php echo $product_link; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-description text-muted small mb-2">
                                        <?php echo mb_strimwidth(htmlspecialchars($product['description']), 0, 50, "..."); ?>
                                    </p>
                                    <div class="rating-stars mb-2">
                                        <?php for($i = 0; $i < 5; $i++): ?>
                                            <?php if($i < $product['rating']): ?>★<?php else: ?>☆<?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="price-group mt-auto">
                                        <span class="current-price"><?php echo number_format($product['price']); ?> đ</span>
                                        <?php if ($discount_percent > 0): ?>
                                            <span class="original-price"><?php echo number_format($product['original_price']); ?> đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Nút Xem chi tiết (thay vì Thêm vào giỏ ngay) -->
                                    <a href="<?php echo $product_link; ?>" class="btn btn-primary w-100 mt-3">Mua ngay</a>
                                </div>
                            </div>
                        </div>
                <?php
                    } // Kết thúc vòng lặp
                } else {
                    echo "<p class='text-center'>Chưa có sản phẩm nào.</p>";
                }
                // Đóng kết nối nếu cần, nhưng footer thường không cần đóng gấp ở đây
                ?>
            </div>
        </div>
    </section>

    <!-- SECTION VỀ CHÚNG TÔI (GIỮ NGUYÊN) -->
    <section class="section-dark">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 style="color: white;" class="section-title">Về Chúng Tôi</h2>
                    <p>Cà Phê Việt được thành lập với sứ mệnh mang đến những sản phẩm cà phê chất lượng cao, giữ trọn hương vị truyền thống của cà phê Việt Nam.</p>
                    <p>Chúng tôi làm việc trực tiếp với nông dân tại các vùng trồng cà phê nổi tiếng để đảm bảo chất lượng từ nông trại đến ly cà phê của bạn.</p>
                </div>
                <div class="col-lg-6">
                    <img src="images/about-us.jpg" class="img-fluid rounded-3" alt="Pha chế cà phê nghệ thuật">
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>