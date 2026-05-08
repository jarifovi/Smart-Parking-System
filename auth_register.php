<?php
// auth_register.php
require_once 'config_database.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $pass1 === '') {
        $errors[] = 'All fields are required.';
    }
    if ($pass1 !== $pass2) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $databaseConnection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already exists.';
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $stmtInsert = $databaseConnection->prepare(
                "INSERT INTO users (full_name, email, password_hash, is_admin) VALUES (?,?,?,0)"
            );
            $stmtInsert->bind_param('sss', $name, $email, $hash);
            if ($stmtInsert->execute()) {
                $newUserId = $stmtInsert->insert_id;
                $databaseConnection->query(
                    "INSERT INTO user_notifications (user_id) VALUES ($newUserId)"
                );
                header('Location: auth_login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Registration failed.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Smart Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css">
    <style>body{padding:0!important;padding-left:0!important;}body::before{display:none;}</style>
</head>
<body class="auth-page-bg d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="container px-3">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-4" style="animation:fadeInDown 0.6s ease-out;">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:72px;height:72px;background:linear-gradient(135deg,#8b5cf6,#3b82f6);box-shadow:0 0 30px rgba(139,92,246,0.4);">
                    <i class="bi bi-person-plus-fill text-white fs-1"></i>
                </div>
                <h2 class="fw-bold text-white mb-1">Create Account</h2>
                <p class="text-secondary small mb-0">Join the Smart Parking network today</p>
            </div>
            <div class="auth-card p-4 p-md-5" style="animation:fadeInUp 0.6s ease-out 0.2s both;">
                <h4 class="mb-1 text-center fw-bold text-white">Sign Up</h4>
                <p class="text-center text-secondary small mb-4">Fill in your details to get started</p>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger small mb-4 border-0">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($e); ?>
                    </div>
                <?php endforeach; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle"><i class="bi bi-person text-secondary"></i></span>
                            <input type="text" name="full_name" class="form-control border-start-0 ps-0" placeholder="John Doe" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle"><i class="bi bi-envelope text-secondary"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle"><i class="bi bi-lock text-secondary"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle"><i class="bi bi-shield-lock text-secondary"></i></span>
                                <input type="password" name="password_confirm" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4 form-check small">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label text-secondary" for="terms">I agree to the <a href="#" style="color:var(--accent-hover);">Terms & Conditions</a></label>
                    </div>
                    <button class="btn btn-primary w-100 py-2 fw-bold">Register Account <i class="bi bi-arrow-right ms-1"></i></button>
                </form>
                <div class="mt-4 text-center small text-secondary">
                    Already have an account? <a href="auth_login.php" class="text-decoration-none fw-bold" style="color:var(--accent-hover);">Sign In</a>
                </div>
            </div>
            <div class="mt-4 text-center text-secondary small" style="animation:fadeInUp 0.6s ease-out 0.4s both;opacity:0.5;">
                &copy; <?php echo date('Y'); ?> Smart Parking System &middot; Crafted by <strong>Jarif Ovi</strong>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
