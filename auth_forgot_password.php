<?php
// auth_forgot_password.php
require_once 'config_database.php'; // same file used by auth_login.php

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } else {
        // Find user by email
        $stmt = $databaseConnection->prepare("
            SELECT id, email 
            FROM users 
            WHERE email = ? 
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            $userId = (int)$user['id'];

            // Generate a secure random token (64 hex chars)
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

            // Store token
            $stmt2 = $databaseConnection->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt2->bind_param('iss', $userId, $token, $expiresAt);
            $stmt2->execute();

            // Build full URL for the reset link
            $baseUrl   = sprintf(
                'http://%s%s',
                $_SERVER['HTTP_HOST'],
                rtrim(dirname($_SERVER['PHP_SELF']), '/\\')
            );
            $resetLink = $baseUrl . '/auth_reset_password.php?token=' . urlencode($token);

            // Try email (will work only if mail is configured)
            $subject = 'Smart Parking - Password Reset';
            $body    = "Hello,\n\nClick the link below to reset your password:\n\n{$resetLink}\n\nIf you did not request this, you can ignore this email.";
            @mail($email, $subject, $body);

            // Message for local testing
            $message = 'If the email exists, a reset link has been sent.<br>'
                     . 'For local testing, you can click here: '
                     . '<a href="' . htmlspecialchars($resetLink) . '">Reset Password</a>';
        } else {
            // Do NOT reveal that email does not exist
            $message = 'If the email exists, a reset link has been sent.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | Smart Parking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css">
    <style>body{padding:0!important;padding-left:0!important;}body::before{display:none;}</style>
</head>
<body class="auth-page-bg d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="container px-3">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4" style="animation:fadeInDown 0.6s ease-out;">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:72px;height:72px;background:linear-gradient(135deg,#f59e0b,#ef4444);box-shadow:0 0 30px rgba(245,158,11,0.4);">
                    <i class="bi bi-key-fill text-white fs-1"></i>
                </div>
                <h2 class="fw-bold text-white mb-1">Recover Access</h2>
                <p class="text-secondary small mb-0">We'll help you get back into your account</p>
            </div>
            <div class="auth-card p-4 p-md-5" style="animation:fadeInUp 0.6s ease-out 0.2s both;">
                <h4 class="mb-1 text-center fw-bold text-white">Forgot Password</h4>
                <p class="text-center text-secondary small mb-4">Enter your email to receive a reset link</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger small mb-4 border-0">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-info small mb-4 border-0">
                        <i class="bi bi-info-circle me-2"></i><?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-4">
                        <label class="form-label text-secondary small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle"><i class="bi bi-envelope text-secondary"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required autofocus>
                        </div>
                        <div class="form-text mt-2">Enter the email associated with your account.</div>
                    </div>
                    <button class="btn btn-primary w-100 py-2 fw-bold">Send Reset Link <i class="bi bi-send ms-1"></i></button>
                </form>

                <div class="mt-4 text-center small text-secondary">
                    Remember your password? <a href="auth_login.php" class="text-decoration-none fw-bold" style="color:var(--accent-hover);">Sign In</a>
                </div>
            </div>
            <div class="mt-4 text-center text-secondary small" style="animation:fadeInUp 0.6s ease-out 0.4s both;opacity:0.5;">
                &copy; <?php echo date('Y'); ?> Smart Parking System &middot; Security First
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
