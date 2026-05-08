<?php
$pageTitle  = 'Session Control Center';
$sidebarKey = 'admin_bookings';
require_once 'helper_layout_admin.php';

$bookingId = $_GET['booking_id'] ?? '';
$status    = $_GET['status'] ?? 'all';
$fromDate  = $_GET['from'] ?? '';
$toDate    = $_GET['to'] ?? '';
$search    = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if($bookingId) $where .= " AND b.id = ".(int)$bookingId;
if($status !== 'all' && $status !== '') $where .= " AND b.status = '".$databaseConnection->real_escape_string($status)."'";
if($fromDate) $where .= " AND b.start_time >= '".$databaseConnection->real_escape_string($fromDate)." 00:00:00'";
if($toDate) $where .= " AND b.start_time <= '".$databaseConnection->real_escape_string($toDate)." 23:59:59'";
if($search) {
    $s = $databaseConnection->real_escape_string($search);
    $where .= " AND (u.full_name LIKE '%$s%' OR b.vehicle_label LIKE '%$s%' OR s.spot_number LIKE '%$s%')";
}

$result = $databaseConnection->query("
    SELECT b.*, u.full_name, u.email, s.spot_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_spots s ON b.spot_id = s.id
    $where
    ORDER BY b.start_time DESC
");
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-info bg-opacity-10 rounded-4 border border-info border-opacity-25">
            <i class="bi bi-cpu-fill text-info fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Session Control Center</h2>
            <p class="text-secondary m-0">Real-time telemetry and state management for all parking nodes.</p>
        </div>
    </div>
</div>

<div class="card mb-5 border-info border-opacity-10">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">NODE ID</label>
                <input type="text" name="booking_id" class="form-control" placeholder="ID" value="<?php echo htmlspecialchars($bookingId); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">STATE</label>
                <select name="status" class="form-control">
                    <option value="all">All States</option>
                    <option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
                    <option value="completed" <?php echo $status==='completed'?'selected':''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">FROM</label>
                <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($fromDate); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">TO</label>
                <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($toDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">IDENTITY SEARCH</label>
                <input type="text" name="search" class="form-control" placeholder="User, Spot, Vehicle" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-1">
                <button class="btn-primary w-100 py-2"><i class="bi bi-radar"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card p-0 overflow-hidden border-info border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50">NODE</th>
                    <th class="small opacity-50">OPERATOR</th>
                    <th class="small opacity-50">SPOT</th>
                    <th class="small opacity-50">VEHICLE</th>
                    <th class="small opacity-50">EPOCH</th>
                    <th class="small opacity-50 text-center">STATUS</th>
                    <th class="text-end pe-4 small opacity-50">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): while ($b = $result->fetch_assoc()): ?>
                    <tr class="align-middle">
                        <td class="ps-4 py-4"><code>#<?php echo $b['id']; ?></code></td>
                        <td>
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($b['full_name']); ?></div>
                            <div class="small text-secondary opacity-50"><?php echo htmlspecialchars($b['email']); ?></div>
                        </td>
                        <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3"><?php echo htmlspecialchars($b['spot_number']); ?></span></td>
                        <td><span class="badge bg-dark border border-secondary border-opacity-25"><?php echo htmlspecialchars($b['vehicle_label']); ?></span></td>
                        <td class="small text-secondary">
                            <div class="fw-bold"><?php echo date('M d, H:i', strtotime($b['start_time'])); ?></div>
                            <div class="opacity-50"><?php echo date('M d, H:i', strtotime($b['end_time'])); ?></div>
                        </td>
                        <td class="text-center">
                            <?php $clr = $b['status']==='active' ? 'success' : 'primary'; ?>
                            <span class="badge bg-<?php echo $clr; ?> bg-opacity-10 text-<?php echo $clr; ?> border border-<?php echo $clr; ?> border-opacity-25 px-3">
                                <?php echo strtoupper($b['status']); ?>
                            </span>
                        </td>
                        <td class="text-end pe-4 fw-800 text-white">$<?php echo number_format($b['amount'], 2); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center py-5">No telemetry records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'helper_layout_footer.php'; ?>
