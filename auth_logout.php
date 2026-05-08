<?php
// auth_logout.php
require_once 'config_database.php';
session_destroy();
header('Location: auth_login.php');
exit;
