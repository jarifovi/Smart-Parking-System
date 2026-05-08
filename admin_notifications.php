<?php
// admin_notifications.php
$pageTitle  = 'Notifications - Admin';
$sidebarKey = ''; // no sidebar item highlighted for this page

require_once 'helper_layout_admin.php';

// Mark all unread notifications as read when admin opens this page
if (!empty($loggedAdmin['id'])) {
    $stmtMark = $databaseConnection->prepare("
        UPDATE user_notifications 
        SET is_read = 1 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmtMark->bind_param('i', $loggedAdmin['id']);
    $stmtMark->execute();
}

// Load notifications for this admin
$notifications = [];
if (!empty($loggedAdmin['id'])) {
    $stmtList = $databaseConnection->prepare("
        SELECT id, message, is_read, created_at
        FROM user_notifications
        WHERE user_id = ?
        ORDER BY created_at DESC, id DESC
    ");
    $stmtList->bind_param('i', $loggedAdmin['id']);
    $stmtList->execute();
    $resultList = $stmtList->get_result();
    while ($row = $resultList->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<div class="page-header-main">
    <div class="page-header-title">System Notifications</div>
    <div class="page-header-sub">View recent activity and automated system alerts.</div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th class="text-end">Timestamp</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($notifications): ?>
                    <?php foreach ($notifications as $n): ?>
                        <tr>
                            <td>#<?php echo $n['id']; ?></td>
                            <td>
                                <div class="<?php echo $n['is_read'] ? 'text-secondary' : 'fw-bold'; ?>">
                                    <?php echo htmlspecialchars($n['message']); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($n['is_read']): ?>
                                    <span class="badge bg-secondary">READ</span>
                                <?php else: ?>
                                    <span class="badge bg-success">NEW</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end small text-secondary">
                                <?php echo date('M d, H:i', strtotime($n['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="bi bi-bell-slash text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">Your inbox is empty.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// helper_layout_admin.php will close tags
?>
