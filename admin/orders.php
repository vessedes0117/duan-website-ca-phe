<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
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
                <a href="orders.php" class="active"><i class="bi bi-receipt me-2"></i> Quản lý Đơn hàng</a>
                <a href="products.php"><i class="bi bi-box-seam me-2"></i> Quản lý Sản phẩm</a>
                <a href="comments.php"><i class="bi bi-chat-dots me-2"></i> Quản lý Bình luận</a>
                <a href="../index.php" target="_blank"><i class="bi bi-house me-2"></i> Xem Website</a>
            </nav>
        </div>

        <div class="col-md-10 bg-light p-4">
            <h2 class="mb-4">Danh Sách Đơn Hàng</h2>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['fullname']); ?><br>
                                            <small class="text-muted"><?php echo $row['phone']; ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td class="fw-bold text-danger"><?php echo number_format($row['total_money']); ?> đ</td>
                                        <td>
                                            <?php 
                                            if($row['payment_method'] == 'BANK') echo '<span class="badge bg-primary">VietQR</span>';
                                            elseif($row['payment_method'] == 'MOMO') echo '<span class="badge bg-danger">MoMo</span>';
                                            else echo '<span class="badge bg-secondary">COD</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $status = $row['status'];
                                            if($status == 'Pending') echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                                            elseif($status == 'Shipping') echo '<span class="badge bg-info text-dark">Đang giao</span>';
                                            elseif($status == 'Completed') echo '<span class="badge bg-success">Hoàn thành</span>';
                                            elseif($status == 'Cancelled') echo '<span class="badge bg-danger">Đã hủy</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">Chưa có đơn hàng nào.</td></tr>
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