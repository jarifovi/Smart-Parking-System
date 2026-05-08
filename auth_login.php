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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Smart Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css">
    <style>
        body { padding: 0 !important; padding-left: 0 !important; }
        body::before { display: none; }
    </style>
</head>
<body class="auth-page-bg d-flex align-items-center justify-content-center" style="min-height:100vh;">

<div class="container px-3">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <!-- Logo -->
            <div class="text-center mb-4" style="animation: fadeInDown 0.6s ease-out;">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:72px;height:72px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);box-shadow:0 0 30px rgba(59,130,246,0.4);">
                    <i class="bi bi-p-circle-fill text-white fs-1"></i>
                </div>
                <h2 class="fw-bold text-white mb-1" style="letter-spacing:-0.5px;">Smart Parking</h2>
                <p class="text-secondary small mb-0">Professional Parking Management System</p>
            </div>

            <!-- Card -->
            <div class="auth-card p-4 p-md-5" style="animation: fadeInUp 0.6s ease-out 0.2s both;">
                <h4 class="mb-1 text-center fw-bold text-white">Welcome Back</h4>
                <p class="text-center text-secondary small mb-4">Sign in to access your dashboard</p>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success small mb-4 border-0">
                        <i class="bi bi-check-circle me-2"></i>Registration successful! Please login.
                    </div>
                <?php endif; ?>

                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger small mb-4 border-0">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($e); ?>
                    </div>
                <?php endforeach; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-600">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                <i class="bi bi-envelope text-secondary"></i>
                            </span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label text-secondary small fw-600">Password</label>
                            <a href="auth_forgot_password.php" class="small text-decoration-none" style="color:var(--accent-hover);">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                <i class="bi bi-lock text-secondary"></i>
                            </span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-bold" style="font-size:1rem;">
                        Sign In <i class="bi bi-arrow-right-short ms-1"></i>
                    </button>
                </form>

                <div class="mt-4 text-center small text-secondary">
                    Don't have an account? <a href="auth_register.php" class="text-decoration-none fw-bold" style="color:var(--accent-hover);">Create one</a>
                </div>
            </div>

            <div class="mt-4 text-center text-secondary small" style="animation: fadeInUp 0.6s ease-out 0.4s both; opacity:0.5;">
                &copy; <?php echo date('Y'); ?> Smart Parking System &middot; Crafted by <strong>Jarif Ovi</strong>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
