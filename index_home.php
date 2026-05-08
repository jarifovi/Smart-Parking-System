<?php
// index_home.php
require_once 'helper_authentication.php';

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
