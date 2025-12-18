<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$error = "";
$success = "";

// XỬ LÝ KHI SUBMIT FORM
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : NULL;
    $stock = intval($_POST['stock_quantity']);
    $category = $_POST['category'];

    // Xử lý upload ảnh
    $image = "default.jpg"; // Ảnh mặc định nếu lỗi
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]); // Thêm time để không trùng tên
        $target_file = $target_dir . $file_name;
        
        // Kiểm tra đuôi file
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $file_name; // Lưu tên file vào biến để insert db
            } else {
                $error = "Có lỗi khi tải ảnh lên.";
            }
        }
    } else {
        $error = "Vui lòng chọn ảnh sản phẩm.";
    }

    if (empty($error)) {
        $sql = "INSERT INTO products (name, description, price, original_price, stock_quantity, category, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddiss", $name, $description, $price, $original_price, $stock, $category, $image);
        
        if ($stmt->execute()) {
            $success = "Thêm sản phẩm thành công!";
        } else {
            $error = "Lỗi Database: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm Mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Thêm Sản Phẩm Mới</h2>
                <a href="products.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="product-add.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Cà phê Arabica...">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Danh mục</label>
                                <select name="category" class="form-select">
                                    <option value="ca-phe-hat">Cà phê hạt</option>
                                    <option value="ca-phe-bot">Cà phê bột</option>
                                    <option value="phin-giay">Phin giấy</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số lượng trong kho</label>
                                <input type="number" name="stock_quantity" class="form-control" value="100" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá gốc (VNĐ) - Để gạch đi</label>
                                <input type="number" name="original_price" class="form-control" placeholder="Không bắt buộc">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh</label>
                            <input type="file" name="image" class="form-control" required accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả sản phẩm</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-save"></i> Lưu Sản Phẩm
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>