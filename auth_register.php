<?php
// 1. LOGIC FIRST
require_once 'config_database.php';
require_once 'helper_authentication.php';

if (userIsLoggedIn()) {
    header('Location: ' . (userIsAdmin() ? 'admin_dashboard_home.php' : 'user_dashboard_home.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    if ($name && $email && $pass) {
        // Check if email exists
        $check = $databaseConnection->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Conflict: Email already registered in the network.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $databaseConnection->prepare("INSERT INTO users (full_name, email, password_hash, is_admin) VALUES (?, ?, ?, 0)");
            $stmt->bind_param("sss", $name, $email, $hashed);
            if ($stmt->execute()) {
                header('Location: auth_login.php?registered=1');
                exit;
            } else { $error = 'System fault: Failed to initialize node.'; }
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
    <title>Register Identity | SP CORE</title>
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
            max-width: 500px;
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
        <div class="auth-card">
            <div class="text-center mb-5">
                <div class="p-3 bg-primary bg-opacity-10 rounded-4 d-inline-flex mb-4 border border-primary border-opacity-25">
                    <i class="bi bi-person-plus-fill text-primary fs-1"></i>
                </div>
                <h2 class="fw-900 text-white m-0" style="letter-spacing: -1px;">Smart <span class="text-primary">Parking</span> System</h2>
                <p class="text-secondary small fw-bold mt-2">CREATE YOUR NETWORK IDENTITY</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4 small">
                    <i class="bi bi-exclamation-octagon me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">FULL LEGAL NAME</label>
                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">NETWORK IDENTITY (EMAIL)</label>
                    <input type="email" name="email" class="form-control" placeholder="user@sp-core.io" required>
                </div>
                <div class="mb-5">
                    <label class="form-label text-secondary small fw-bold">SECURITY KEY (PASSWORD)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary w-100 py-3 mb-4">
                    REGISTER IDENTITY <i class="bi bi-plus-lg ms-2"></i>
                </button>

                <div class="text-center">
                    <p class="text-secondary small mb-0">Already in the network? <a href="auth_login.php" class="text-primary text-decoration-none fw-bold">Initialize Session</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
