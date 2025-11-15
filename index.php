<?php require_once 'includes/header.php'; ?>

<main>
    <!-- HERO BANNER -->
    <section class="hero-banner text-center d-flex align-items-center">
        <div class="container">
            <h1 class="display-4">Cà Phê Việt Nam</h1>
            <p class="lead">Hương vị đậm đà, truyền thống thuần túy</p>
            <a href="products.php" class="btn btn-primary btn-lg mx-2">Khám phá sản phẩm</a>
            <a href="#featured-products" class="btn btn-outline-light btn-lg mx-2">Xem thêm</a>
        </div>
    </section>

    <!-- SECTION SẢN PHẨM NỔI BẬT -->
    <section id="featured-products" class="section-light">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <p class="section-subtitle">Những sản phẩm cà phê chất lượng cao, được chọn lọc kỹ càng từ vùng trồng cà phê nổi tiếng của Việt Nam.</p>
            </div>

            <div class="row">
                <!-- Sản phẩm 1: Cà Phê Chồn -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="product-card">
                        <div class="product-card-img-container">
                            <img src="images/ca-phe-chon.jpg" class="card-img-top" alt="Cà Phê Chồn">
                            <div class="discount-badge">-18%</div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Cà Phê Chồn Legend</h5>
                            <p class="card-description">Hương vị độc đáo và quý hiếm bậc nhất, được mệnh danh là "vua cà phê".</p>
                            <div class="rating-stars">★★★★★</div>
                            <div class="price-group">
                                <span class="current-price">990,000 đ</span>
                                <span class="original-price">1,200,000 đ</span>
                            </div>
                            <a href="#" class="btn btn-primary">Thêm vào giỏ</a>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm 2: Robusta Buôn Ma Thuột -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="product-card">
                        <div class="product-card-img-container">
                            <img src="images/bmt-robusta.jpg" class="card-img-top" alt="Cà phê Robusta Buôn Ma Thuột">
                            <div class="discount-badge">-15%</div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Robusta Buôn Ma Thuột</h5>
                            <p class="card-description">Vị đậm đà, mạnh mẽ đặc trưng của vùng đất bazan, hàm lượng caffeine cao.</p>
                            <div class="rating-stars">★★★★★</div>
                            <div class="price-group">
                                <span class="current-price">170,000 đ</span>
                                <span class="original-price">200,000 đ</span>
                            </div>
                            <a href="#" class="btn btn-primary">Thêm vào giỏ</a>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm 3: Arabica Cầu Đất -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="product-card">
                        <div class="product-card-img-container">
                            <img src="images/cau-dat-arabica.jpg" class="card-img-top" alt="Cà phê Arabica Cầu Đất">
                            <div class="discount-badge">-10%</div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Arabica Cầu Đất</h5>
                            <p class="card-description">Hương thơm quyến rũ, vị chua thanh tao và hậu vị ngọt ngào từ cao nguyên Đà Lạt.</p>
                            <div class="rating-stars">★★★★☆</div>
                            <div class="price-group">
                                <span class="current-price">225,000 đ</span>
                                <span class="original-price">250,000 đ</span>
                            </div>
                            <a href="#" class="btn btn-primary">Thêm vào giỏ</a>
                        </div>
                    </div>
                </div>
                
                <!-- Sản phẩm 4: Cà Phê Phin Giấy -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="product-card">
                         <div class="product-card-img-container">
                            <img src="images/phin-giay.jpg" class="card-img-top" alt="Cà Phê Phin Giấy">
                            <div class="discount-badge">-20%</div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Cà Phê Phin Giấy</h5>
                            <p class="card-description">Tiện lợi cho dân văn phòng, giữ trọn hương vị cà phê phin truyền thống.</p>
                            <div class="rating-stars">★★★★☆</div>
                             <div class="price-group">
                                <span class="current-price">120,000 đ</span>
                                <span class="original-price">150,000 đ</span>
                            </div>
                            <a href="#" class="btn btn-primary">Thêm vào giỏ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION VỀ CHÚNG TÔI -->
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