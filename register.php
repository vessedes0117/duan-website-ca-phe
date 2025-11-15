<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db_connection.php';
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($fullname)) { $errors[] = "Họ và tên là bắt buộc."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Định dạng email không hợp lệ."; }
    if (strlen($password) < 6) { $errors[] = "Mật khẩu phải có ít nhất 6 ký tự."; }
    if ($password !== $confirm_password) { $errors[] = "Mật khẩu nhập lại không khớp."; }
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email này đã được sử dụng.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $fullname, $email, $hashed_password);
            if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = "Đăng ký tài khoản thành công! Vui lòng đăng nhập.";
                header("Location: login.php");
                exit();
            } else { $errors[] = "Đã có lỗi xảy ra. Vui lòng thử lại."; }
            $stmt_insert->close();
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
                        <h4>Đăng Ký</h4>
                        <p class="mb-0">Tạo tài khoản mới để bắt đầu!</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="fullname" class="form-label fw-bold">Họ và Tên</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required placeholder="Nhập họ và tên">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập email của bạn">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Tối thiểu 6 ký tự">
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">Nhập lại mật khẩu</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Xác nhận lại mật khẩu">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                        </form>
                         <div class="text-center mt-4">
                            <p class="text-muted mb-0">Đã có tài khoản? <a href="login.php" class="fw-bold" style="color: var(--color-primary);">Đăng nhập ngay</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>