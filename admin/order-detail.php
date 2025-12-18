<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// XỬ LÝ CẬP NHẬT TRẠNG THÁI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    echo "<script>alert('Cập nhật trạng thái thành công!');</script>";
}

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) { die("Đơn hàng không tồn tại"); }

// Lấy chi tiết sản phẩm
$sql_details = "SELECT od.*, p.name, p.image 
                FROM order_details od 
                JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = $order_id";
$details = $conn->query($sql_details);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chi Tiết Đơn Hàng #<?php echo $order_id; ?></h2>
        <a href="orders.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Danh sách sản phẩm</h5>
                </div>
                <div class="card-body">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $details->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../images/<?php echo htmlspecialchars($item['image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" class="me-2">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price']); ?> đ</td>
                                <td>x <?php echo $item['quantity']; ?></td>
                                <td class="fw-bold"><?php echo number_format($item['price'] * $item['quantity']); ?> đ</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="fw-bold text-danger fs-5"><?php echo number_format($order['total_money']); ?> đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['fullname']); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    <p><strong>Ghi chú:</strong> <?php echo !empty($order['note']) ? htmlspecialchars($order['note']) : 'Không có'; ?></p>
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">Xử lý đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Phương thức thanh toán:</label>
                        <div class="fs-5 fw-bold text-primary mt-1">
                            <?php 
                                if($order['payment_method'] == 'BANK') echo '<i class="bi bi-qr-code"></i> Chuyển khoản VietQR';
                                elseif($order['payment_method'] == 'MOMO') echo '<i class="bi bi-wallet2"></i> Ví MoMo';
                                else echo '<i class="bi bi-cash"></i> Tiền mặt (COD)';
                            ?>
                        </div>
                    </div>

                    <hr>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Trạng thái hiện tại:</label>
                            <select name="status" id="status" class="form-select form-select-lg">
                                <option value="Pending" <?php if($order['status']=='Pending') echo 'selected'; ?>>Chờ xử lý</option>
                                <option value="Shipping" <?php if($order['status']=='Shipping') echo 'selected'; ?>>Đang giao hàng</option>
                                <option value="Completed" <?php if($order['status']=='Completed') echo 'selected'; ?>>Hoàn thành</option>
                                <option value="Cancelled" <?php if($order['status']=='Cancelled') echo 'selected'; ?>>Đã hủy</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-success w-100 py-2">
                            <i class="bi bi-check-lg"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>