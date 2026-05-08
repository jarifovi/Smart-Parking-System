<?php
require_once 'config_database.php';

$email = 'admin@gmail.com';
$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $databaseConnection->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "<div style='font-family: sans-serif; padding: 20px; background: #dcfce7; color: #166534; border-radius: 8px;'>";
    echo "<h3>✅ Admin Password Reset Successful!</h3>";
    echo "<p>You can now login with:</p>";
    echo "<ul><li><b>Email:</b> admin@gmail.com</li><li><b>Password:</b> admin123</li></ul>";
    echo "<p><a href='auth_login.php'>Go to Login Page</a></p>";
    echo "</div>";
} else {
    echo "Error updating password: " . $databaseConnection->error;
}
?>
