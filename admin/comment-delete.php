<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Xóa bình luận
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: comments.php?msg=deleted");
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
} else {
    header("Location: comments.php");
}
exit();
?>