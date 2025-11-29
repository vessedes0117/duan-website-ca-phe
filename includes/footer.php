<footer class="site-footer">
            <div class="container">
                <div class="row">
                    <!-- Cột 1: Giới thiệu -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <!-- THAY ĐỔI Ở ĐÂY: THÊM LOGO -->
                        <a href="index.php">
                            <img src="images/logo.png" alt="Logo Cà Phê Việt" class="mb-3" style="height: 35px;">
                        </a>
                        <p>Mang đến hương vị cà phê Việt Nam chất lượng cao, truyền thống và đậm đà.</p>
                    </div>

                    <!-- Cột 2: Sản phẩm -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h5>Sản phẩm</h5>
                        <ul class="list-unstyled">
                            <li><a href="#">Cà phê bột</a></li>
                            <li><a href="#">Cà phê hạt</a></li>
                            <li><a href="#">Cà phê pha sẵn</a></li>
                        </ul>
                    </div>

                    <!-- Cột 3: Hỗ trợ -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5>Hỗ trợ</h5>
                        <ul class="list-unstyled">
                            <li><a href="#">Liên hệ</a></li>
                            <li><a href="#">Chính sách đổi trả</a></li>
                            <li><a href="#">Hướng dẫn mua hàng</a></li>
                        </ul>
                    </div>

                    <!-- Cột 4: Theo dõi -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5>Theo dõi chúng tôi</h5>
                        <p>Kết nối để không bỏ lỡ các ưu đãi và sản phẩm mới nhất.</p>
                    </div>
                </div>

                <div class="text-center footer-bottom">
                    <p class="mb-0">&copy; <?php echo date("Y"); ?> Cà Phê Việt. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- POPUP THÔNG BÁO THÊM GIỎ HÀNG THÀNH CÔNG -->
        <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
              <div class="modal-header border-0 justify-content-end pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body pb-4">
                <div class="text-success mb-3" style="font-size: 3rem;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h4 class="fw-bold">Thành công!</h4>
                <p class="text-muted">Sản phẩm đã được thêm vào giỏ hàng.</p>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Mua tiếp</button>
                    <a href="cart.php" class="btn btn-primary rounded-pill px-4">Xem giỏ hàng</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Link tới Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <!-- SCRIPT XỬ LÝ CHUNG TOÀN WEBSITE -->
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. XỬ LÝ NÚT "THÊM VÀO GIỎ" (AJAX)
            const btnAdd = document.getElementById('btnAddToCart');
            if (btnAdd) {
                btnAdd.addEventListener('click', function() {
                    // Lấy dữ liệu từ form
                    const form = document.getElementById('productForm');
                    const formData = new FormData(form);

                    // Gửi AJAX
                    fetch('ajax_add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Cập nhật số lượng trên menu (Badge)
                            const badge = document.getElementById('cart-badge');
                            if(badge) {
                                badge.innerText = data.total_count;
                                badge.style.display = 'inline-block';
                            }
                            
                            // Hiện Popup thông báo
                            var myModal = new bootstrap.Modal(document.getElementById('cartModal'));
                            myModal.show();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }

            // 2. LOGIC ĐÁNH GIÁ SAO (STAR RATING)
            const stars = document.querySelectorAll('.star-icon');
            const ratingInput = document.getElementById('ratingInput');
            const ratingText = document.getElementById('ratingText');
            const ratingLabels = {
                1: "Tệ", 2: "Không hài lòng", 3: "Bình thường", 4: "Hài lòng", 5: "Tuyệt vời"
            };

            if(stars.length > 0 && ratingInput) {
                function highlightStars(count) {
                    stars.forEach(star => {
                        const value = star.getAttribute('data-value');
                        if(value <= count) {
                            star.classList.add('bi-star-fill');
                            star.classList.remove('bi-star');
                        } else {
                            star.classList.add('bi-star');
                            star.classList.remove('bi-star-fill');
                        }
                    });
                }
                // Mặc định hiển thị theo value
                highlightStars(ratingInput.value);

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        ratingInput.value = value;
                        highlightStars(value);
                        if(ratingText) ratingText.innerText = ratingLabels[value];
                    });
                    
                    star.addEventListener('mouseover', function() {
                        highlightStars(this.getAttribute('data-value'));
                    });
                });

                const group = document.getElementById('starRatingGroup');
                if(group) {
                    group.addEventListener('mouseleave', function() {
                        highlightStars(ratingInput.value);
                    });
                }
            }
        });

        // 3. CÁC HÀM GLOBAL (Tăng/Giảm số lượng, Đổi ảnh)
        function increaseQty() { 
            var el = document.getElementById('qtyInput');
            if(el) el.value++;
        }
        function decreaseQty() { 
            var el = document.getElementById('qtyInput');
            if(el && el.value > 1) el.value--; 
        }
        function changeImage(el) {
            var mainImg = document.getElementById('mainImg');
            if(mainImg) {
                mainImg.src = el.src;
                mainImg.style.opacity = 0;
                setTimeout(function(){ mainImg.style.opacity = 1; }, 100);
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                el.classList.add('active');
            }
        }
        </script>
    </body>
</html>