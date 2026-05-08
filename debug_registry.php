<?php
require_once 'config_database.php';

// --- TITAN NETWORK DISCOVERY ---
if (isset($_GET['repair'])) {
    $email = $_GET['repair'];
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $databaseConnection->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed, $email);
    $stmt->execute();
    $repaired = true;
}

$res = $databaseConnection->query("SELECT full_name, email, is_admin FROM users");
?>
<!doctype html>
<html lang="en">
<head>
    <style>
        body { background: #020617; color: #f8fafc; font-family: 'Outfit', sans-serif; padding: 3rem; }
        .node-card { background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(251, 191, 36, 0.2); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .btn-sync { background: #fbbf24; color: #020617; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-weight: bold; font-size: 0.8rem; }
        .status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 1rem; background: rgba(251, 191, 36, 0.1); color: #fbbf24; }
    </style>
</head>
<body>
    <h2 style="color: #fbbf24;">🛰️ TITAN NETWORK DISCOVERY</h2>
    <p style="color: #94a3b8;">Scanning user registry for valid access nodes...</p>

    <?php if (isset($repaired)): ?>
        <div style="background: #10b98122; color: #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            [REPAIR COMPLETE] Password for <?php echo htmlspecialchars($email); ?> has been synced to: <strong>admin123</strong>
        </div>
    <?php endif; ?>

    <?php while($u = $res->fetch_assoc()): ?>
        <div class="node-card">
            <div>
                <div style="font-weight: bold;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                <div style="font-size: 0.9rem; color: #94a3b8;"><?php echo htmlspecialchars($u['email']); ?></div>
                <?php if($u['is_admin']): ?><span class="status-badge">ADMIN NODE</span><?php endif; ?>
            </div>
            <a href="?repair=<?php echo urlencode($u['email']); ?>" class="btn-sync">SYNC PASSWORD</a>
        </div>
    <?php endwhile; ?>

    <div style="margin-top: 2rem;">
        <a href="auth_login.php" style="color: #fbbf24;">← Back to Login Portal</a>
    </div>
</body>
</html>
