<?php
$pageTitle  = 'Bookings Management';
$sidebarKey = 'admin_bookings';
require_once 'helper_layout_admin.php';

$bookingId = trim($_GET['booking_id'] ?? '');
$status    = $_GET['status'] ?? 'all';
$fromDate  = $_GET['from'] ?? '';
$toDate    = $_GET['to'] ?? '';
$search    = trim($_GET['search'] ?? '');

$where = 'WHERE 1=1';
if ($bookingId !== '') {
    $id = (int)$bookingId;
    $where .= " AND b.id=$id";
}
if ($status !== 'all') {
    $safeStatus = $databaseConnection->real_escape_string($status);
    $where .= " AND b.status='$safeStatus'";
}
if ($fromDate !== '') {
    $fromSql = $databaseConnection->real_escape_string($fromDate . ' 00:00:00');
    $where .= " AND b.start_time >= '$fromSql'";
}
if ($toDate !== '') {
    $toSql = $databaseConnection->real_escape_string($toDate . ' 23:59:59');
    $where .= " AND b.end_time <= '$toSql'";
}
if ($search !== '') {
    $s = $databaseConnection->real_escape_string($search);
    $where .= " AND (u.full_name LIKE '%$s%' OR u.email LIKE '%$s%' OR p.spot_number LIKE '%$s%' OR b.vehicle_label LIKE '%$s%')";
}

$result = $databaseConnection->query("
    SELECT b.*, u.full_name, u.email, p.spot_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_spots p ON b.spot_id = p.id
    $where
    ORDER BY b.start_time DESC
");
?>
<div class="page-header-main">
    <div class="page-header-title">Bookings Management</div>
    <div class="page-header-sub">Monitor and manage all system reservations across the entire facility.</div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">Booking ID</label>
                <input class="form-control" name="booking_id" placeholder="e.g. 1024" value="<?php echo htmlspecialchars($bookingId); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="all">All Statuses</option>
                    <option value="active"    <?php echo $status==='active'?'selected':''; ?>>Active</option>
                    <option value="completed" <?php echo $status==='completed'?'selected':''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status==='cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From Date</label>
                <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($fromDate); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To Date</label>
                <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($toDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search Query</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input class="form-control border-start-0 ps-0" name="search" placeholder="User, Spot, Vehicle" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Spot</th>
                    <th>Vehicle</th>
                    <th>Time Period</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows): while ($b=$result->fetch_assoc()): ?>
                    <?php
                    $badgeClass = 'bg-secondary';
                    if ($b['status'] === 'active')     $badgeClass = 'bg-success';
                    if ($b['status'] === 'completed')  $badgeClass = 'bg-primary';
                    if ($b['status'] === 'cancelled')  $badgeClass = 'bg-danger';
                    ?>
                    <tr>
                        <td>#<?php echo $b['id']; ?></td>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($b['full_name']); ?></div>
                            <div class="small text-secondary"><?php echo htmlspecialchars($b['email']); ?></div>
                        </td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($b['spot_number']); ?></span></td>
                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($b['vehicle_label']); ?></span></td>
                        <td class="small">
                            <div><?php echo date('M d, H:i', strtotime($b['start_time'])); ?></div>
                            <div class="opacity-50"><?php echo date('M d, H:i', strtotime($b['end_time'])); ?></div>
                        </td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                        <td class="text-end fw-bold">$<?php echo number_format($b['amount'], 2); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-calendar-x text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No matching reservations found.</div>
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

