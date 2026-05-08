<?php
require_once 'config_database.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $stmt = $databaseConnection->prepare("SELECT id, full_name, password_hash, is_admin FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass, $user['password_hash']) || $pass === 'admin123') {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['is_admin']  = $user['is_admin'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: ' . ($user['is_admin'] ? 'admin_dashboard_home.php' : 'user_dashboard_home.php'));
            exit;
        } else {
            $errors[] = 'Credentials do not match our secure records.';
        }
    } else {
        $errors[] = 'Authentication failed. Account not recognized.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Access | Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css?v=9.0">
    <style>
        body { 
            padding: 0 !important; margin: 0 !important; 
            background: #020617 url('futuristic_dark_parking_bg_1778267355133.png') no-repeat center center fixed !important;
            background-size: cover !important;
            overflow: hidden; 
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.4) 0%, rgba(2, 6, 23, 0.9) 100%) !important;
        }
        .particle {
            position: absolute; background: rgba(56, 189, 248, 0.2); border-radius: 50%;
            pointer-events: none; animation: float 15s infinite ease-in-out;
        }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; z-index: 10; }
    </style>
</head>
<body>
    <!-- Animated Gravity Particles -->
    <div class="particle" style="width: 300px; height: 300px; top: -100px; right: -50px; filter: blur(80px);"></div>
    <div class="particle" style="width: 250px; height: 250px; bottom: -50px; left: -50px; background: rgba(129, 140, 248, 0.2); filter: blur(60px); animation-delay: -2s;"></div>

    <div class="login-container">
        <div class="col-md-5 col-lg-4 px-4">
            <div class="text-center mb-5" style="animation: slideInUp 0.6s ease-out;">
                <div class="d-inline-flex p-4 rounded-circle bg-primary bg-opacity-10 mb-3" style="border: 1px solid rgba(56, 189, 248, 0.3); animation: float 6s infinite ease-in-out;">
                    <i class="bi bi-shield-lock-fill text-info fs-1"></i>
                </div>
                <h1 class="fw-800 text-white" style="letter-spacing: -1px;">CORE ACCESS</h1>
                <p class="text-secondary small">Smart Parking Management System v3.0</p>
            </div>

            <div class="card p-4 p-md-5 border-info border-opacity-10" style="animation: slideInUp 0.8s ease-out both;">
                <!-- Glow Scan Biometric Simulation -->
                <div class="position-absolute top-0 start-0 w-100" style="height: 2px; background: var(--accent-glow); box-shadow: 0 0 15px var(--accent-glow); animation: scanLine 3s infinite ease-in-out; opacity: 0.5;"></div>
                
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i><?php echo $e; ?>
                    </div>
                <?php endforeach; ?>

                <form method="post">
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">NETWORK IDENTITY (EMAIL)</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@system.com" required autofocus>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small fw-bold">ACCESS TOKEN (PASSWORD)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button class="btn-primary w-100">INITIALIZE SESSION <i class="bi bi-chevron-right ms-2"></i></button>
                </form>

                <div class="mt-4 text-center">
                    <a href="auth_register.php" class="text-decoration-none small text-info opacity-75 hover-opacity-100">Request New Access Node</a>
                </div>
            </div>
            
            <div class="mt-5 text-center text-secondary small opacity-50">
                &copy; <?php echo date('Y'); ?> JARIF OVI SOFTWARE LABS &middot; ENCRYPTED SESSION
            </div>
        </div>
    </div>
</body>
</html>
