<?php
// Start session to access $_SESSION data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config_database.php';
require_once 'helper_authentication.php';

header('Content-Type: application/json');

// Using the correct function name: userIsLoggedIn()
if (!userIsLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Handle Mark as Read
if (isset($_GET['mark_read'])) {
    $databaseConnection->query("UPDATE user_notifications SET is_read = 1 WHERE user_id = $userId");
    echo json_encode(['status' => 'success']);
    exit;
}

// Fetch Latest 5 Notifications
$result = $databaseConnection->query("
    SELECT * FROM user_notifications 
    WHERE user_id = $userId 
    ORDER BY created_at DESC LIMIT 5
");

$notifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'message' => $row['message'],
            'is_read' => (bool)$row['is_read'],
            'time' => date('M d, H:i', strtotime($row['created_at']))
        ];
    }
}

$unreadCount = 0;
$unreadRes = $databaseConnection->query("
    SELECT COUNT(*) as count FROM user_notifications 
    WHERE user_id = $userId AND is_read = 0
");
if ($unreadRes) {
    $unreadCount = (int)$unreadRes->fetch_assoc()['count'];
}

echo json_encode([
    'status' => 'success',
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
