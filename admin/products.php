<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
        .product-img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4 class="fw-bold text-white">Admin Panel</h4>
            </div>
            <nav class="mt-3">
                <a href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="orders.php"><i class="bi bi-receipt me-2"></i> Quản lý Đơn hàng</a>
                <a href="products.php" class="active"><i class="bi bi-box-seam me-2"></i> Quản lý Sản phẩm</a>
                <a href="comments.php"><i class="bi bi-chat-dots me-2"></i> Quản lý Bình luận</a>
                <a href="../index.php" target="_blank"><i class="bi bi-house me-2"></i> Xem Website</a>
            </nav>
        </div>

        <div class="col-md-10 bg-light p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Danh Sách Sản Phẩm</h2>
                <a href="product-add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm Sản Phẩm Mới
                </a>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá bán</th>
                                <th>Kho</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" class="product-img-thumb" alt="">
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <?php 
                                            // Hiển thị tên đẹp hơn cho danh mục
                                            $cats = [
                                                'ca-phe-hat' => 'Cà phê hạt', 
                                                'ca-phe-bot' => 'Cà phê bột', 
                                                'phin-giay' => 'Phin giấy'
                                            ];
                                            echo isset($cats[$row['category']]) ? $cats[$row['category']] : $row['category'];
                                            ?>
                                        </td>
                                        <td class="text-danger fw-bold"><?php echo number_format($row['price']); ?> đ</td>
                                        <td>
                                            <?php if($row['stock_quantity'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $row['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Hết hàng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="product-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="product-delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">Chưa có sản phẩm nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>