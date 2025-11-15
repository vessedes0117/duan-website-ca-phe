<?php
require_once 'includes/db_connection.php';
require_once 'includes/header.php';

// --- LOGIC LỌC VÀ SẮP XẾP SẢN PHẨM ---

// 1. Lấy các tham số từ URL
$current_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// 2. Chuẩn bị câu lệnh SQL
$sql = "SELECT * FROM products";
$params = []; // Mảng chứa các tham số cho bind_param
$types = "";  // Chuỗi chứa kiểu dữ liệu của tham số

// 3. Xử lý LỌC THEO DANH MỤC (Thêm điều kiện WHERE)
if ($current_category != 'all') {
    $sql .= " WHERE category = ?";
    $params[] = $current_category;
    $types .= "s";
}

// 4. Xử lý SẮP XẾP (Thêm điều kiện ORDER BY)
switch ($current_sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    case 'rating_desc':
        $sql .= " ORDER BY rating DESC";
        break;
    default:
        $sql .= " ORDER BY id DESC"; // Sắp xếp mặc định
        break;
}

// 5. Chuẩn bị và thực thi câu lệnh an toàn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <!-- PAGE HEADER -->
    <section class="page-header">
        <div class="container">
            <h1>Tất Cả Sản Phẩm</h1>
            <p>Khám phá bộ sưu tập cà phê Việt Nam chất lượng cao với nhiều loại và hương vị đa dạng.</p>
        </div>
    </section>

    <!-- NỘI DUNG CHÍNH: BỘ LỌC VÀ LƯỚI SẢN PHẨM -->
    <section class="section-light">
        <div class="container">
            <div class="row">

                <!-- CỘT BỘ LỌC (SIDEBAR) -->
                <div class="col-lg-3">
                    <div class="filter-sidebar">
                        <h5>Danh Mục</h5>
                        <ul class="filter-list">
                            <li>
                                <a href="products.php" class="<?php echo ($current_category == 'all') ? 'active' : ''; ?>">Tất cả sản phẩm</a>
                            </li>
                            <li>
                                <a href="products.php?category=ca-phe-hat" class="<?php echo ($current_category == 'ca-phe-hat') ? 'active' : ''; ?>">Cà phê hạt</a>
                            </li>
                            <li>
                                <a href="products.php?category=ca-phe-bot" class="<?php echo ($current_category == 'ca-phe-bot') ? 'active' : ''; ?>">Cà phê bột</a>
                            </li>
                            <li>
                                <a href="products.php?category=phin-giay" class="<?php echo ($current_category == 'phin-giay') ? 'active' : ''; ?>">Cà phê phin giấy</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- CỘT LƯỚI SẢN PHẨM -->
                <div class="col-lg-9">
                    <!-- HÀNG CHỨA BỘ SẮP XẾP -->
                    <div class="row mb-3">
                        <div class="col-12 d-flex justify-content-end">
                            <form action="products.php" method="GET" id="sort-form" class="d-flex align-items-center">
                                <!-- Input ẩn để giữ lại bộ lọc danh mục khi sắp xếp -->
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($current_category); ?>">
                                
                                <label for="sort-select" class="me-2 fw-bold">Sắp xếp:</label>
                                <select class="form-select w-auto" id="sort-select" name="sort">
                                    <option value="default" <?php echo ($current_sort == 'default') ? 'selected' : ''; ?>>Mặc định</option>
                                    <option value="price_asc" <?php echo ($current_sort == 'price_asc') ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                                    <option value="price_desc" <?php echo ($current_sort == 'price_desc') ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
                                    <option value="name_asc" <?php echo ($current_sort == 'name_asc') ? 'selected' : ''; ?>>Tên: A-Z</option>
                                    <option value="rating_desc" <?php echo ($current_sort == 'rating_desc') ? 'selected' : ''; ?>>Đánh giá cao nhất</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <?php
                        if ($result->num_rows > 0) {
                            while($product = $result->fetch_assoc()) {
                                // (Mã HTML của thẻ sản phẩm không thay đổi)
                                $discount_percent = 0;
                                if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
                                    $discount_percent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                }
                        ?>
                                <div class="col-md-6 col-lg-4 mb-4">
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
                                                <?php for($i = 0; $i < 5; $i++): ?><?php if($i < $product['rating']): ?>★<?php else: ?>☆<?php endif; ?><?php endfor; ?>
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
                            echo "<div class='col-12'><p class='text-center fs-4'>Không có sản phẩm nào trong danh mục này.</p></div>";
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </div>
                </div> <!-- Kết thúc cột lưới sản phẩm -->
            </div> <!-- Kết thúc .row chính -->
        </div> <!-- Kết thúc .container -->
    </section>
</main>

<script>
    // Lắng nghe sự kiện 'change' trên dropdown
    document.getElementById('sort-select').addEventListener('change', function() {
        // Tự động submit form khi người dùng chọn một tùy chọn mới
        document.getElementById('sort-form').submit();
    });
</script>

<?php require_once 'includes/footer.php'; ?>