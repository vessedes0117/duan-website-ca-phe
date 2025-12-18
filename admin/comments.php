<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách bình luận (Kết nối với bảng Users và Products để lấy tên)
$sql = "SELECT c.*, u.fullname, p.name as product_name, p.image 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        JOIN products p ON c.product_id = p.id 
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bình Luận</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
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
                <a href="products.php"><i class="bi bi-box-seam me-2"></i> Quản lý Sản phẩm</a>
                <a href="comments.php" class="active"><i class="bi bi-chat-dots me-2"></i> Quản lý Bình luận</a>
                <a href="../index.php" target="_blank"><i class="bi bi-house me-2"></i> Xem Website</a>
            </nav>
        </div>

        <div class="col-md-10 bg-light p-4">
            <h2 class="mb-4">Danh Sách Đánh Giá & Bình Luận</h2>
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">Đã xóa bình luận thành công!</div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Khách hàng</th>
                                <th>Sản phẩm</th>
                                <th>Đánh giá</th>
                                <th style="width: 30%;">Nội dung</th>
                                <th>Ngày đăng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" class="me-2">
                                                <span class="small"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-warning">
                                                <?php 
                                                for($i=1; $i<=5; $i++) {
                                                    echo ($i <= $row['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted fst-italic">"<?php echo htmlspecialchars($row['content']); ?>"</span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="comment-delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.');">
                                                <i class="bi bi-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Chưa có bình luận nào.</td></tr>
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