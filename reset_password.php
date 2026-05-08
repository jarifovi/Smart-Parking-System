<?php
// reset_password.php
// Handle password reset using a token

require_once 'config.php'; // same DB include as forget page

$token  = $_GET['token'] ?? '';
$error  = '';
$info   = '';
$valid  = false;
$userId = null;

if ($token) {
    // Look up token
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

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $newPassword     = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirm'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        // ⚠️ IMPORTANT: change `password` below to your actual password column name
        $stmtU = $databaseConnection->prepare("
            UPDATE users 
            SET password = ?
            WHERE id = ?
        ");
        $stmtU->bind_param('si', $hash, $userId);

        if ($stmtU->execute()) {
            // mark reset as used
            $stmtR = $databaseConnection->prepare("
                UPDATE password_resets 
                SET used_at = NOW() 
                WHERE token = ?
            ");
            $stmtR->bind_param('s', $token);
            $stmtR->execute();

            $info = 'Password has been updated. You can now log in.';
            $valid = false; // hide form
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center">
                    <strong>Reset Password</strong>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($info): ?>
                        <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($info); ?></div>
                        <div class="mt-3 text-center small">
                            <a href="login.php">Go to login</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($valid): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label small">New password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Confirm password</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Update Password
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!$valid && !$info && !$error): ?>
                        <div class="alert alert-danger py-2 small">
                            Invalid or expired reset link.
                        </div>
                        <div class="mt-3 text-center small">
                            <a href="forgot_password.php">Request a new reset link</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
