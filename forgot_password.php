<?php
// forgot_password.php
// Show a form to request password reset by email

// TODO: change this to your actual DB include file if different
require_once 'config.php'; // must define $databaseConnection (mysqli)

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = 'Please enter your email address.';
    } else {
        // Find user by email
        $stmt = $databaseConnection->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            $userId = (int)$user['id'];

            // Create token
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Store token
            $stmt2 = $databaseConnection->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt2->bind_param('iss', $userId, $token, $expiresAt);
            $stmt2->execute();

            // Build reset link
            // Change URL base if your project path is different
            $resetLink = 'http://localhost/smart_parking_system/reset_password.php?token=' . urlencode($token);

            // Try sending email (will need mail setup to really work)
            $subject = 'Smart Parking - Password Reset';
            $body    = "Hello,\n\nClick the link below to reset your password:\n\n{$resetLink}\n\nIf you did not request this, ignore this email.";
            @mail($email, $subject, $body);

            // Also show the link on screen (useful for local development)
            $message = 'If the email exists, a reset link has been sent.<br>
                        For local testing, you can click here: 
                        <a href="'.htmlspecialchars($resetLink).'">Reset Password</a>';
        } else {
            // For security, do NOT say "email not found"
            $message = 'If the email exists, a reset link has been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center">
                    <strong>Forgot Password</strong>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="alert alert-info py-2 small"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label small">Email address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Send Reset Link
                        </button>
                    </form>

                    <div class="mt-3 text-center small">
                        <a href="login.php">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
