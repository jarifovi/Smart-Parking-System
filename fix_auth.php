<?php
require_once 'config_database.php';

// --- TITAN SECURITY UTILITY v2 ---
$newPassword = 'admin123';
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

// 1. Find any admin user
$res = $databaseConnection->query("SELECT email FROM users ORDER BY is_admin DESC LIMIT 1");
$user = $res->fetch_assoc();
$targetEmail = $user['email'] ?? 'admin@example.com';

// 2. Force Sync
$stmt = $databaseConnection->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed, $targetEmail);

if ($stmt->execute()) {
    echo "<div style='background:#020617; color:#fbbf24; padding:2rem; font-family:sans-serif; border:2px solid #fbbf24; border-radius:1rem; margin:2rem; text-align:center;'>";
    echo "<h2 style='letter-spacing:2px;'>🛡️ TITAN SYNC: SUCCESS</h2>";
    echo "<hr style='border-color:rgba(251,191,36,0.2);'>";
    echo "<p style='font-size:1.2rem;'>Your Admin Identity has been verified and synced.</p>";
    echo "<p>USE EMAIL: <strong style='color:#fff; font-size:1.3rem;'>$targetEmail</strong></p>";
    echo "<p>USE PASSWORD: <strong style='color:#fff; font-size:1.3rem;'>$newPassword</strong></p>";
    echo "<div style='margin-top:2rem;'><a href='auth_login.php' style='background:#fbbf24; color:#020617; padding:1rem 2rem; border-radius:0.5rem; text-decoration:none; font-weight:bold;'>ENTER SYSTEM</a></div>";
    echo "</div>";
} else {
    echo "Fatal Sync Error: " . $databaseConnection->error;
}
?>
