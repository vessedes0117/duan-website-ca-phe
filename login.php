<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db_connection.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập cả email và mật khẩu.";
    } else {
        // CẬP NHẬT: Lấy thêm cột 'role' để kiểm tra quyền Admin
        $sql = "SELECT id, fullname, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Lưu thông tin vào Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_fullname'] = $user['fullname'];
                $_SESSION['user_role'] = $user['role']; // Lưu quyền hạn

                // PHÂN QUYỀN CHUYỂN HƯỚNG
                if ($user['role'] == 1) {
                    // Nếu là Admin -> Vào trang quản trị
                    header("Location: admin/index.php");
                } else {
                    // Nếu là Khách -> Về trang chủ
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Email hoặc mật khẩu không chính xác.";
            }
        } else {
            $error = "Email hoặc mật khẩu không chính xác.";
        }
        $stmt->close();
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<main class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="auth-card">
                    <div class="card-header">
                        <h4>Đăng Nhập</h4>
                        <p class="mb-0">Chào mừng bạn quay lại!</p>
                    </div>
                    <div class="card-body">
                        <?php 
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                            unset($_SESSION['success_message']);
                        }
                        if (!empty($error)) {
                            echo '<div class="alert alert-danger">' . $error . '</div>';
                        }
                        ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập email của bạn">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Nhập mật khẩu">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                        </form>
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Chưa có tài khoản? <a href="register.php" class="fw-bold" style="color: var(--color-primary);">Đăng ký ngay</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>