<?php
// helper_authentication.php
require_once 'config_database.php';

function userIsLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function userIsAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin(): void {
    if (!userIsLoggedIn()) {
        header('Location: auth_login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!userIsAdmin()) {
        header('Location: user_dashboard_home.php');
        exit;
    }
}

function getLoggedUser(mysqli $databaseConnection): ?array {
    if (!userIsLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $result = $databaseConnection->query("SELECT * FROM users WHERE id = $id LIMIT 1");
    return $result && $result->num_rows ? $result->fetch_assoc() : null;
}
?>
