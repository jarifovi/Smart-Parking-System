<?php
require_once 'config_database.php';
require_once 'helper_authentication.php';

// Smart Routing Node
if (userIsLoggedIn()) {
    if (userIsAdmin()) {
        header('Location: admin_dashboard_home.php');
    } else {
        header('Location: user_dashboard_home.php');
    }
} else {
    header('Location: auth_login.php');
}
exit;
