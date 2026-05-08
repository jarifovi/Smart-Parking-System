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
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    if ($email && $pass) {
        $stmt = $databaseConnection->prepare("SELECT id, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($u = $res->fetch_assoc()) {
            if (password_verify($pass, $u['password_hash'])) {
                $_SESSION['user_id']  = $u['id'];
                $_SESSION['is_admin'] = (bool)$u['is_admin'];
                header('Location: ' . ($u['is_admin'] ? 'admin_dashboard_home.php' : 'user_dashboard_home.php'));
                exit;
            } else { $error = 'Neural mismatch: Credentials rejected.'; }
        } else { $error = 'Node not found: Identity unrecognized.'; }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="manifest.json">
    <title>Secure Login | SP CORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css?v=10.0">
    <style>
        .scan-line {
            position: fixed; top: -10%; left: 0; width: 100%; height: 10%;
            background: linear-gradient(to bottom, transparent, var(--accent-main), transparent);
            z-index: 10000; display: none;
            box-shadow: 0 0 20px var(--accent-main);
            opacity: 0.5;
        }
        @keyframes scan {
            from { top: -10%; }
            to { top: 110%; }
        }
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
    <div class="scan-line" id="scanner"></div>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-5">
                <div class="p-3 bg-primary bg-opacity-10 rounded-4 d-inline-flex mb-4 border border-primary border-opacity-25">
                    <i class="bi bi-shield-lock-fill text-primary fs-1"></i>
                </div>
                <h2 class="fw-900 text-white m-0" style="letter-spacing: -1px;">Smart <span class="text-primary">Parking</span> System</h2>
                <p class="text-secondary small fw-bold mt-2">VANGUARD OPERATING SYSTEM</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4 small">
                    <i class="bi bi-exclamation-octagon me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="post" onsubmit="runScan(event)">
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">NETWORK IDENTITY (EMAIL)</label>
                    <input type="email" name="email" class="form-control" placeholder="operator@sp-core.io" required autofocus>
                </div>
                <div class="mb-5">
                    <label class="form-label text-secondary small fw-bold">SECURITY KEY (PASSWORD)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary w-100 py-3 mb-4">
                    INITIALIZE SESSION <i class="bi bi-chevron-right ms-2"></i>
                </button>

                <div class="text-center">
                    <p class="text-secondary small mb-0">New node in the network? <a href="auth_register.php" class="text-primary text-decoration-none fw-bold">Register Identity</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function runScan(e) {
            e.preventDefault();
            const scanner = document.getElementById('scanner');
            scanner.style.display = 'block';
            scanner.style.animation = 'scan 1.5s linear infinite';
            setTimeout(() => {
                document.getElementById('loginForm').submit();
            }, 1500);
        }
    </script>
</body>
</html>
