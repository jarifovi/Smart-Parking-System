<?php
$pageTitle  = 'Revenue Ledger';
$sidebarKey = 'admin_payments';
require_once 'helper_layout_admin.php';

$search = trim($_GET['search'] ?? '');
$method = $_GET['method'] ?? 'all';
$status = $_GET['pay_status'] ?? 'all';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

$where = 'WHERE 1=1';
if ($search !== '') {
    $s = $databaseConnection->real_escape_string($search);
    $where .= " AND (p.transaction_id LIKE '%$s%' OR b.vehicle_label LIKE '%$s%' OR u.full_name LIKE '%$s%')";
}
if ($method !== 'all') { $m = $databaseConnection->real_escape_string($method); $where .= " AND p.payment_method='$m'"; }
if ($status !== 'all') { $ps = $databaseConnection->real_escape_string($status); $where .= " AND p.payment_status='$ps'"; }
if ($from !== '') { $fromSql = $databaseConnection->real_escape_string($from.' 00:00:00'); $where .= " AND p.created_at >= '$fromSql'"; }
if ($to !== '') { $toSql = $databaseConnection->real_escape_string($to.' 23:59:59'); $where .= " AND p.created_at <= '$toSql'"; }

$summary = $databaseConnection->query("
    SELECT 
        (SELECT COUNT(*) FROM payments) AS total_payments,
        (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE payment_status='paid') AS paid_total,
        (IFNULL((SELECT SUM(amount) FROM payments WHERE payment_status='pending'), 0) + IFNULL((SELECT SUM(amount) FROM bookings WHERE status='Active'), 0)) AS pending_total
")->fetch_assoc();

$result = $databaseConnection->query("
    SELECT p.*, b.vehicle_label, u.full_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.created_at DESC
");
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25">
            <i class="bi bi-wallet2 text-success fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Revenue Ledger</h2>
            <p class="text-secondary m-0">Detailed transaction telemetry and financial node audit.</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-info border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">TOTAL TRANSACTIONS</div>
            <div class="stat-card-number"><?php echo number_format($summary['total_payments']); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-success border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">CONFIRMED REVENUE</div>
            <div class="stat-card-number text-success">$<?php echo number_format($summary['paid_total'], 2); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-warning border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">OUTSTANDING BALANCE</div>
            <div class="stat-card-number text-warning">$<?php echo number_format($summary['pending_total'], 2); ?></div>
        </div>
    </div>
</div>

<div class="card mb-5 border-primary border-opacity-10">
    <div class="card-body">
        <form class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">SEARCH QUERY</label>
                <input class="form-control" name="search" placeholder="Txn, Vehicle, User" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">METHOD</label>
                <select name="method" class="form-control">
                    <option value="all">All Methods</option>
                    <option value="Cash" <?php echo $method==='Cash'?'selected':''; ?>>Cash</option>
                    <option value="Card" <?php echo $method==='Card'?'selected':''; ?>>Card</option>
                    <option value="PayPal" <?php echo $method==='PayPal'?'selected':''; ?>>PayPal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">STATUS</label>
                <select name="pay_status" class="form-control">
                    <option value="all">All Statuses</option>
                    <option value="paid" <?php echo $status==='paid'?'selected':''; ?>>Paid</option>
                    <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">FROM</label>
                <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($from); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-bold">TO</label>
                <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($to); ?>">
            </div>
            <div class="col-md-1">
                <button class="btn-primary w-100 py-2"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card p-0 overflow-hidden border-info border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50">TXN ID</th>
                    <th class="small opacity-50">USER IDENTITY</th>
                    <th class="small opacity-50">VEHICLE</th>
                    <th class="small opacity-50">METHOD</th>
                    <th class="small opacity-50">TIMESTAMP</th>
                    <th class="text-end pe-4 small opacity-50">GROSS AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): while ($p=$result->fetch_assoc()): ?>
                    <tr class="align-middle">
                        <td class="ps-4 py-4"><code><?php echo htmlspecialchars($p['transaction_id']); ?></code></td>
                        <td class="fw-bold text-white"><?php echo htmlspecialchars($p['full_name']); ?></td>
                        <td><span class="badge bg-dark border border-secondary border-opacity-25"><?php echo htmlspecialchars($p['vehicle_label']); ?></span></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?php echo $p['payment_method']; ?></span></td>
                        <td class="small text-secondary"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                        <td class="text-end pe-4 fw-800 text-success">$<?php echo number_format($p['amount'], 2); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center py-5">No payment records detected in the ledger.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'helper_layout_footer.php'; ?>
