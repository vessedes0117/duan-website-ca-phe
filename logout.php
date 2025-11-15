<?php
session_start(); // Bắt đầu session

// Hủy tất cả các biến session
session_unset();

// Phá hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>