<?php
$pageTitle  = 'Payments Management';
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
if ($method !== 'all') {
    $m = $databaseConnection->real_escape_string($method);
    $where .= " AND p.payment_method='$m'";
}
if ($status !== 'all') {
    $ps = $databaseConnection->real_escape_string($status);
    $where .= " AND p.payment_status='$ps'";
}
if ($from !== '') {
    $fromSql = $databaseConnection->real_escape_string($from.' 00:00:00');
    $where .= " AND p.created_at >= '$fromSql'";
}
if ($to !== '') {
    $toSql = $databaseConnection->real_escape_string($to.' 23:59:59');
    $where .= " AND p.created_at <= '$toSql'";
}

$summary = $databaseConnection->query("
    SELECT 
        -- Total payment records
        (SELECT COUNT(*) FROM payments) AS total_payments,
        
        -- Total paid from payments table
        (SELECT IFNULL(SUM(amount), 0) 
         FROM payments 
         WHERE payment_status='paid') AS paid_total,
        
        -- Pending = pending payments + active bookings
        (
            IFNULL((SELECT SUM(amount) FROM payments WHERE payment_status='pending'), 0)
            +
            IFNULL((SELECT SUM(amount) FROM bookings WHERE status='Active'), 0)
        ) AS pending_total
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
<div class="page-header-main">
    <div class="page-header-title">Payments Management</div>
    <div class="page-header-sub">Track financial transactions, revenue collection, and payment statuses.</div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Total Transactions</div>
                <div class="stat-card-number"><?php echo number_format($summary['total_payments']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Confirmed Revenue</div>
                <div class="stat-card-number text-success">$<?php echo number_format($summary['paid_total'], 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Outstanding Balance</div>
                <div class="stat-card-number text-warning">$<?php echo number_format($summary['pending_total'], 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Search Query</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input class="form-control border-start-0 ps-0" name="search" placeholder="Txn, Vehicle, User" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Method</label>
                <select name="method" class="form-select">
                    <option value="all">All Methods</option>
                    <option value="Cash"        <?php echo $method==='Cash'?'selected':''; ?>>Cash</option>
                    <option value="Card"        <?php echo $method==='Card'?'selected':''; ?>>Card</option>
                    <option value="PayPal"      <?php echo $method==='PayPal'?'selected':''; ?>>PayPal</option>
                    <option value="BankTransfer"<?php echo $method==='BankTransfer'?'selected':''; ?>>Bank Transfer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="pay_status" class="form-select">
                    <option value="all">All Statuses</option>
                    <option value="paid"    <?php echo $status==='paid'?'selected':''; ?>>Paid</option>
                    <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date From</label>
                <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($from); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date To</label>
                <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($to); ?>">
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
                    <th>Transaction ID</th>
                    <th>User</th>
                    <th>Vehicle</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows): while ($p=$result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td><code><?php echo htmlspecialchars($p['transaction_id']); ?></code></td>
                        <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['vehicle_label']); ?></span></td>
                        <td class="fw-bold">$<?php echo number_format($p['amount'], 2); ?></td>
                        <td><?php echo $p['payment_method']; ?></td>
                        <td class="small"><?php echo date('M d, Y H:i', strtotime($p['created_at'])); ?></td>
                        <td>
                            <?php if ($p['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">PAID</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">PENDING</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-wallet2 text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No payment records found.</div>
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
