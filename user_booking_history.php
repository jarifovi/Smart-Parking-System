<?php
$pageTitle  = 'Booking History';
$sidebarKey = 'user_history';
require_once 'helper_layout_user.php';

$userId = (int)$_SESSION['user_id'];

$res = $databaseConnection->query("
    SELECT b.*, p.spot_number
    FROM bookings b
    JOIN parking_spots p ON b.spot_id = p.id
    WHERE b.user_id = $userId
    ORDER BY b.start_time DESC
");
?>
<h3 class="mb-4">Booking History</h3>

<div class="sp-table-wrapper">
    <table class="table table-striped table-sm sp-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Spot</th>
            <th>Vehicle</th>
            <th>Duration</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows): while ($r=$res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['spot_number']); ?></td>
                <td><?php echo htmlspecialchars($r['vehicle_label']); ?></td>
                <td><?php echo htmlspecialchars($r['start_time'].' - '.$r['end_time']); ?></td>
                <td>$<?php echo number_format($r['amount'],2); ?></td>
                <td><?php echo ucfirst($r['status']); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted">No booking history found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</div></body></html>

