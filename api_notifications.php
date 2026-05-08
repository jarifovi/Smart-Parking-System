<?php
require_once 'helper_database.php';
require_once 'helper_authentication.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

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
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'is_read' => (bool)$row['is_read'],
        'time' => date('M d, H:i', strtotime($row['created_at']))
    ];
}

$unreadCount = $databaseConnection->query("
    SELECT COUNT(*) as count FROM user_notifications 
    WHERE user_id = $userId AND is_read = 0
")->fetch_assoc()['count'];

echo json_encode([
    'status' => 'success',
    'unread_count' => (int)$unreadCount,
    'notifications' => $notifications
]);
