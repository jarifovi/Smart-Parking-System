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
<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-info bg-opacity-10 rounded-4 border border-info border-opacity-25">
            <i class="bi bi-calendar-check text-info fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Session Control Center</h2>
            <p class="text-secondary m-0">Live monitoring of all active and historical parking nodes.</p>
        </div>
    </div>
</div>

<div class="card mb-5 border-info border-opacity-10">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">BOOKING ID</label>
                <input type="text" name="booking_id" class="form-control" placeholder="e.g. 1024" value="<?php echo htmlspecialchars($bookingId); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">STATUS</label>
                <select name="status" class="form-control">
                    <option value="all" <?php echo $status==='all'?'selected':''; ?>>All Statuses</option>
                    <option value="active"    <?php echo $status==='active'?'selected':''; ?>>Active</option>
                    <option value="completed" <?php echo $status==='completed'?'selected':''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status==='cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">FROM DATE</label>
                <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($fromDate); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">TO DATE</label>
                <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($toDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">SEARCH QUERY</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-10"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="User, Spot, Vehicle" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100 py-2"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card p-0 overflow-hidden border-info border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50">ID</th>
                    <th class="small opacity-50">User</th>
                    <th class="small opacity-50">Spot</th>
                    <th class="small opacity-50">Vehicle</th>
                    <th class="small opacity-50">Time Period</th>
                    <th class="small opacity-50">Status</th>
                    <th class="text-end pe-4 small opacity-50">Amount</th>
                </tr>
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

