<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = "";
$success = "";

// XỬ LÝ KHI BẤM NÚT LƯU (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : NULL;
    $stock = intval($_POST['stock_quantity']);
    $category = $_POST['category'];
    $current_image = $_POST['current_image']; // Ảnh cũ

    // Xử lý upload ảnh mới (nếu có)
    $image = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $file_name; // Cập nhật tên ảnh mới
            } else {
                $error = "Có lỗi khi tải ảnh lên.";
            }
        }
    }

    if (empty($error)) {
        $sql = "UPDATE products SET name=?, description=?, price=?, original_price=?, stock_quantity=?, category=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddissi", $name, $description, $price, $original_price, $stock, $category, $image, $id);
        
        if ($stmt->execute()) {
            $success = "Cập nhật sản phẩm thành công!";
            // Cập nhật lại biến $current_image để hiển thị ngay ảnh mới
            $current_image = $image;
        } else {
            $error = "Lỗi Database: " . $conn->error;
        }
    }
}

// LẤY THÔNG TIN SẢN PHẨM HIỆN TẠI
$sql_get = "SELECT * FROM products WHERE id = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$product = $stmt_get->get_result()->fetch_assoc();

if (!$product) { die("Sản phẩm không tồn tại."); }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản Phẩm: <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Sửa Sản Phẩm</h2>
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
                    <form action="product-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Danh mục</label>
                                <select name="category" class="form-select">
                                    <option value="ca-phe-hat" <?php if($product['category'] == 'ca-phe-hat') echo 'selected'; ?>>Cà phê hạt</option>
                                    <option value="ca-phe-bot" <?php if($product['category'] == 'ca-phe-bot') echo 'selected'; ?>>Cà phê bột</option>
                                    <option value="phin-giay" <?php if($product['category'] == 'phin-giay') echo 'selected'; ?>>Phin giấy</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số lượng trong kho</label>
                                <input type="number" name="stock_quantity" class="form-control" required value="<?php echo $product['stock_quantity']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                                <input type="number" name="price" class="form-control" required value="<?php echo $product['price']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá gốc (VNĐ)</label>
                                <input type="number" name="original_price" class="form-control" value="<?php echo $product['original_price']; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh</label>
                            <div class="d-flex align-items-center mb-2">
                                <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;" class="me-3 border">
                                <div class="small text-muted">Ảnh hiện tại</div>
                            </div>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Chỉ chọn nếu bạn muốn thay đổi ảnh mới.</div>
                            <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả sản phẩm</label>
                            <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-save"></i> Cập Nhật Sản Phẩm
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