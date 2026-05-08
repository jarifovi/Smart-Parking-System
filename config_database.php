<?php
// config_database.php
$databaseHost = 'localhost';
$databaseUser = 'root';
$databasePass = '';      // change if needed
$databaseName = 'smart_parking_system';

$databaseConnection = new mysqli($databaseHost, $databaseUser, $databasePass, $databaseName);

if ($databaseConnection->connect_error) {
    die('Database connection failed: ' . $databaseConnection->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
