<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $p_id = intval($_POST['product_id']);
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($qty < 1) $qty = 1;

    // Logic thêm vào giỏ hàng (giống hệt cart.php nhưng không redirect)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$p_id])) {
        $_SESSION['cart'][$p_id] += $qty;
    } else {
        $_SESSION['cart'][$p_id] = $qty;
    }

    // Tính tổng số lượng để cập nhật icon giỏ hàng
    $total_count = array_sum($_SESSION['cart']);

    // Trả về JSON để Javascript đọc
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
        'total_count' => $total_count
    ]);
    exit();
}
?>