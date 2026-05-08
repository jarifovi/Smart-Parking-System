<?php
require_once 'config_database.php';

$email = 'admin@gmail.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$name = 'Admin User';

// Check if user exists
$check = $databaseConnection->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing
    $stmt = $databaseConnection->prepare("UPDATE users SET password_hash = ?, is_admin = 1 WHERE email = ?");
    $stmt->bind_param("ss", $hash, $email);
    $action = "Updated existing admin user";
} else {
    // Create new
    $stmt = $databaseConnection->prepare("INSERT INTO users (full_name, email, password_hash, is_admin) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $name, $email, $hash);
    $action = "Created new admin user";
}

echo "<div style='font-family: sans-serif; padding: 30px; max-width: 500px; margin: 50px auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);'>";
if ($stmt->execute()) {
    echo "<h2 style='color: #10b981; margin-top: 0;'>✅ Success!</h2>";
    echo "<p style='color: #64748b;'>Action: <b>$action</b></p>";
    echo "<div style='background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p style='margin: 5px 0;'><b>Email:</b> <code style='color: #3b82f6;'>admin@gmail.com</code></p>";
    echo "<p style='margin: 5px 0;'><b>Password:</b> <code style='color: #3b82f6;'>admin123</code></p>";
    echo "</div>";
    echo "<a href='auth_login.php' style='display: block; text-align: center; background: #3b82f6; color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold;'>Back to Login</a>";
} else {
    echo "<h2 style='color: #ef4444;'>❌ Error</h2>";
    echo "<p>" . $databaseConnection->error . "</p>";
}
echo "</div>";
?>
