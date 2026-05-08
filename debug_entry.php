<?php
require_once 'config_database.php';

// Manually fetch the admin user from the DB
$res = $databaseConnection->query("SELECT * FROM users WHERE email = 'admin@gmail.com' LIMIT 1");
$user = $res->fetch_assoc();

if ($user) {
    // Manually set the session variables
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['is_admin']  = $user['is_admin'];
    $_SESSION['full_name'] = $user['full_name'];

    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
    echo "<h2 style='color: #3b82f6;'>🚀 Bypassing Login Form...</h2>";
    echo "<p>Session variables set for: <b>" . htmlspecialchars($user['full_name']) . "</b></p>";
    echo "<p>Redirecting to dashboard in 2 seconds...</p>";
    echo "</div>";
    
    header("Refresh: 2; url=admin_dashboard_home.php");
} else {
    echo "Error: Admin user not found in database.";
}
?>
