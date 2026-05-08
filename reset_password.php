<?php
// 1. LOGIC FIRST
require_once 'config_database.php';
require_once 'helper_authentication.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if ($email) {
        // Mock recovery logic for the Titan Suite
        $stmt = $databaseConnection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $success = "Recovery sequence initialized. Check your network mail for the secure key.";
        } else {
            $error = "Node mismatch: Email not found in the global registry.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="manifest.json">
    <title>Security Recovery | SP CORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css?v=10.0">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            background: var(--glass-surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 2rem;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeInScale 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card text-center">
            <div class="mb-5">
                <div class="p-3 bg-primary bg-opacity-10 rounded-4 d-inline-flex mb-4 border border-primary border-opacity-25">
                    <i class="bi bi-key-fill text-primary fs-1"></i>
                </div>
                <h2 class="fw-900 text-white m-0" style="letter-spacing: -1px;">SECURITY <span class="text-primary">RECOVERY</span></h2>
                <p class="text-secondary small fw-bold mt-2">RESTORE YOUR NETWORK ACCESS</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4 small">
                    <i class="bi bi-shield-slash me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4 rounded-4 small">
                    <i class="bi bi-envelope-check me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-5 text-start">
                    <label class="form-label text-secondary small fw-bold">REGISTERED EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="user@sp-core.io" required autofocus>
                    <div class="form-text text-secondary x-small mt-2">We will send a high-security bypass link to this node.</div>
                </div>

                <button type="submit" class="btn-primary w-100 py-3 mb-4">
                    SEND RECOVERY KEY <i class="bi bi-send-fill ms-2"></i>
                </button>

                <div class="text-center">
                    <a href="auth_login.php" class="text-primary text-decoration-none fw-bold small"><i class="bi bi-arrow-left me-2"></i>Return to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
