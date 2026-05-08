<?php
// auth_reset_password.php
require_once 'config_database.php';

$token  = $_GET['token'] ?? '';
$error  = '';
$info   = '';
$valid  = false;
$userId = null;

if ($token !== '') {
    // Look up this token
    $stmt = $databaseConnection->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.email
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $now = time();
        if ($row['used_at'] !== null) {
            $error = 'This reset link has already been used.';
        } elseif (strtotime($row['expires_at']) < $now) {
            $error = 'This reset link has expired.';
        } else {
            $valid  = true;
            $userId = (int)$row['user_id'];
        }
    } else {
        $error = 'Invalid reset token.';
    }
} else {
    $error = 'Missing reset token.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid && $userId) {
    $newPassword     = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirm'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        // IMPORTANT: your column name is password_hash (from auth_login.php)
        $stmtU = $databaseConnection->prepare("
            UPDATE users 
            SET password_hash = ?
            WHERE id = ?
        ");
        $stmtU->bind_param('si', $hash, $userId);

        if ($stmtU->execute()) {
            // Mark reset as used
            $stmtR = $databaseConnection->prepare("
                UPDATE password_resets 
                SET used_at = NOW()
                WHERE token = ?
            ");
            $stmtR->bind_param('s', $token);
            $stmtR->execute();

            $info  = 'Password has been updated. You can now log in.';
            $valid = false; // hide the form
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password | Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="ui_theme_main.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .reset-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
<div class="container px-3">
    <div class="reset-card mx-auto">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-shield-lock fs-2"></i>
            </div>
            <h3 class="text-white fw-bold">Reset Password</h3>
            <p class="text-secondary small">Ensure your new password is secure and unique.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($info): ?>
            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small mb-4">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($info); ?>
            </div>
            <div class="text-center mt-4">
                <a href="auth_login.php" class="btn btn-primary w-100 py-2 fw-bold">Return to Login</a>
            </div>
        <?php endif; ?>

        <?php if ($valid): ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label text-secondary small">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                            <i class="bi bi-key text-secondary"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Min. 6 characters" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                            <i class="bi bi-shield-check text-secondary"></i>
                        </span>
                        <input type="password" name="password_confirm" class="form-control border-start-0 ps-0" placeholder="Repeat password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3">Update Security Credentials</button>
            </form>
        <?php endif; ?>

        <?php if (!$valid && !$info && !$error): ?>
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning small mb-4">
                <i class="bi bi-link-45deg me-2"></i>Invalid or expired reset token.
            </div>
            <div class="text-center mt-4">
                <a href="auth_forgot_password.php" class="text-decoration-none small">Request a new link</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
