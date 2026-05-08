<?php
$pageTitle  = 'User Terminal';
$sidebarKey = 'user_dashboard';
require_once 'helper_layout_user.php';

// Fetch user's active/recent bookings
$userId = $_SESSION['user_id'];
$recentBookings = $databaseConnection->query("
    SELECT b.*, s.spot_number 
    FROM bookings b
    JOIN parking_spots s ON b.spot_id = s.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC LIMIT 5
");

// Aggregates for user stats
$stats = $databaseConnection->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_count,
        IFNULL(SUM(amount), 0) as total_spent
    FROM bookings WHERE user_id = $userId
")->fetch_assoc();
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-info bg-opacity-10 rounded-4 border border-info border-opacity-25">
            <i class="bi bi-shield-check text-info fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Welcome Back, <?php echo explode(' ', $loggedUser['full_name'])[0]; ?></h2>
            <p class="text-secondary m-0">System status: All services operational. Secure node access granted.</p>
        </div>
    </div>
    <!-- Loyalty Hub Node -->
    <div class="col-lg-4">
        <div class="card h-100 border-primary border-opacity-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-gem me-2 text-primary"></i>LOYALTY HUB</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 x-small fw-bold">SILVER RANK</span>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small fw-bold">VANGUARD POINTS</span>
                    <span class="text-white fw-900"><?php echo (int)($loggedUser['loyalty_points'] ?? 0); ?> / 1000</span>
                </div>
                <div class="vanguard-progress">
                    <div class="vanguard-progress-bar" style="width: <?php echo min(100, (int)($loggedUser['loyalty_points'] ?? 0) / 10); ?>%"></div>
                </div>
            </div>
            <p class="x-small text-secondary m-0">Earn 50 more points to unlock <span class="text-info">Premium Node Pricing</span>.</p>
        </div>
    </div>
</div>

<div class="row g-4 mt-2 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-info border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">ACTIVE RESERVATIONS</div>
            <div class="stat-card-number"><?php echo $stats['active_count']; ?></div>
            <div class="mt-3"><a href="user_bookings_list.php" class="text-info text-decoration-none small fw-bold">MANAGE ACTIVE NODES →</a></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-info border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">TOTAL SESSIONS</div>
            <div class="stat-card-number text-white"><?php echo $stats['total']; ?></div>
            <div class="mt-3 text-secondary small">Lifetime engagement</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-info border-opacity-10">
            <div class="small text-secondary mb-2 fw-bold">TOTAL INVESTED</div>
            <div class="stat-card-number text-success">$<?php echo number_format($stats['total_spent'], 2); ?></div>
            <div class="mt-3 text-secondary small">Wallet & Transaction history</div>
        </div>
    </div>
</div>

<div class="card border-info border-opacity-10 p-0 overflow-hidden">
    <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-white opacity-75">MY RECENT RESERVATIONS</h5>
        <a href="user_bookings_list.php" class="btn btn-sm btn-outline-info border-opacity-25 px-3">Full History</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50 py-3">NODE ID</th>
                    <th class="small opacity-50 py-3">SPOT</th>
                    <th class="small opacity-50 py-3">STATUS</th>
                    <th class="small opacity-50 py-3">BOOKED ON</th>
                    <th class="text-end pe-4 small opacity-50 py-3">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentBookings && $recentBookings->num_rows): while($b = $recentBookings->fetch_assoc()): ?>
                <tr class="align-middle">
                    <td class="ps-4 py-3"><code>#<?php echo $b['id']; ?></code></td>
                    <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?php echo htmlspecialchars($b['spot_number']); ?></span></td>
                    <td>
                        <?php $s = strtolower($b['status']); ?>
                        <span class="badge bg-<?php echo $s==='active'?'success':'secondary'; ?> bg-opacity-10 text-<?php echo $s==='active'?'success':'secondary'; ?> border border-<?php echo $s==='active'?'success':'secondary'; ?> border-opacity-25 px-3">
                            <?php echo strtoupper($b['status']); ?>
                        </span>
                    </td>
                    <td class="small text-secondary"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                    <td class="text-end pe-4 fw-800 text-white">$<?php echo number_format($b['amount'], 2); ?></td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-5">No reservations found in your history.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'helper_layout_footer.php'; ?>
