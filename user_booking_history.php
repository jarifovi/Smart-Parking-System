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
<div class="page-header-main">
    <div class="page-header-title">Booking History</div>
    <div class="page-header-sub">Review all your past parking reservations.</div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Spot</th>
                    <th>Vehicle</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($res && $res->num_rows): while ($r=$res->fetch_assoc()):
                    $badge = 'bg-secondary';
                    if ($r['status'] === 'active') $badge = 'bg-success';
                    if ($r['status'] === 'completed') $badge = 'bg-primary';
                    if ($r['status'] === 'cancelled') $badge = 'bg-danger';
                ?>
                    <tr>
                        <td>#<?php echo $r['id']; ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($r['spot_number']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['vehicle_label']); ?></td>
                        <td class="small">
                            <div><?php echo date('M d, H:i', strtotime($r['start_time'])); ?></div>
                            <div class="text-secondary"><?php echo date('M d, H:i', strtotime($r['end_time'])); ?></div>
                        </td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo strtoupper($r['status']); ?></span></td>
                        <td class="text-end fw-bold">$<?php echo number_format($r['amount'],2); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-clock-history text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No booking history found.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php // helper_layout_user.php closes tags ?>
