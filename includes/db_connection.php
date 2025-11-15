<?php
// Thông tin kết nối CSDL
$servername = "localhost";
$username = "root"; // Tên người dùng CSDL mặc định của XAMPP
$password = ""; // Mật khẩu CSDL mặc định của XAMPP là rỗng
$dbname = "db_caphe"; // Tên CSDL bạn đã tạo

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Thiết lập charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");
?>