<?php
// auth_login.php
require_once 'config_database.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $databaseConnection->prepare("
        SELECT id, full_name, password_hash, is_admin 
        FROM users 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass, $user['password_hash'])) {
            // Login OK
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['is_admin']  = $user['is_admin'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['is_admin']) {
                header('Location: admin_dashboard_home.php');
            } else {
                header('Location: user_dashboard_home.php');
            }
            exit;
        }
    }

    $errors[] = 'Invalid email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css">
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-parking text-white fs-2"></i>
                </div>
                <h2 class="fw-bold text-white mb-1">Smart Parking</h2>
                <p class="text-secondary small">Professional Parking Management System</p>
            </div>
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4 text-center fw-bold">Sign In</h4>

                    <?php if (isset($_GET['registered'])): ?>
                        <div class="alert alert-success small mb-4">
                            <i class="bi bi-check-circle me-2"></i>Registration successful. Please login.
                        </div>
                    <?php endif; ?>

                    <?php foreach ($errors as $e): ?>
                        <div class="alert alert-danger small mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($e); ?>
                        </div>
                    <?php endforeach; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-envelope text-secondary"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Password</label>
                                <a href="auth_forgot_password.php" class="small text-decoration-none text-accent">Forgot?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-lock text-secondary"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 py-2 fw-bold">
                            Login <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                    </form>

                    <div class="mt-4 text-center small text-secondary">
                        Don't have an account? <a href="auth_register.php" class="text-accent fw-bold text-decoration-none">Create one</a>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 text-center text-secondary small">
                <p>&copy; <?php echo date('Y'); ?> Smart Parking System. <br> Crafted for Excellence.</p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
,div href="mt-2 text-center small"
,div class="auth_forget_password.php"
no mt-3 text-center small "a href="auth_register.php">Create one</a>

