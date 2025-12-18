<?php
session_start();
require_once '../includes/db_connection.php'; // Kết nối CSDL từ thư mục cha

// 1. KIỂM TRA QUYỀN ADMIN
// Nếu chưa đăng nhập hoặc role không phải là 1 (Admin) thì đá về trang login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// 2. LẤY SỐ LIỆU THỐNG KÊ
// Tổng đơn hàng
$sql_orders = "SELECT COUNT(*) as total FROM orders";
$total_orders = $conn->query($sql_orders)->fetch_assoc()['total'];

// Tổng doanh thu (Chỉ tính đơn đã Hoàn thành - Completed, nhưng tạm thời tính hết để bạn vui)
$sql_revenue = "SELECT SUM(total_money) as total FROM orders";
$revenue = $conn->query($sql_revenue)->fetch_assoc()['total'];

// Số sản phẩm
$sql_products = "SELECT COUNT(*) as total FROM products";
$total_products = $conn->query($sql_products)->fetch_assoc()['total'];

// Số khách hàng
$sql_users = "SELECT COUNT(*) as total FROM users WHERE role = 0";
$total_customers = $conn->query($sql_users)->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cà Phê Việt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Sidebar đơn giản */
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: white;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4 class="fw-bold">Admin Panel</h4>
                <small>Xin chào, <?php echo $_SESSION['user_fullname']; ?></small>
            </div>
            <nav class="mt-3">
                <a href="index.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="orders.php"><i class="bi bi-receipt me-2"></i> Quản lý Đơn hàng</a>
                <a href="products.php"><i class="bi bi-box-seam me-2"></i> Quản lý Sản phẩm</a>
                <a href="comments.php"><i class="bi bi-chat-dots me-2"></i> Quản lý Bình luận</a>
                <a href="../index.php" target="_blank"><i class="bi bi-house me-2"></i> Xem Website</a>
                <a href="../logout.php" class="text-danger mt-3"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a>
            </nav>
        </div>

        <div class="col-md-10 bg-light p-4">
            <h2 class="mb-4">Tổng Quan Hệ Thống</h2>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Doanh Thu</h6>
                                <h3 class="fw-bold my-2"><?php echo number_format($revenue); ?> đ</h3>
                            </div>
                            <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card bg-success text-white border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Tổng Đơn Hàng</h6>
                                <h3 class="fw-bold my-2"><?php echo $total_orders; ?></h3>
                            </div>
                            <i class="bi bi-cart-check fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-dark border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Sản Phẩm</h6>
                                <h3 class="fw-bold my-2"><?php echo $total_products; ?></h3>
                            </div>
                            <i class="bi bi-cup-hot fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white border-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Khách Hàng</h6>
                                <h3 class="fw-bold my-2"><?php echo $total_customers; ?></h3>
                            </div>
                            <i class="bi bi-people fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-arrow-up-circle"></i> Chọn mục "Quản lý Đơn hàng" ở menu bên trái để xử lý đơn khách đặt.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>