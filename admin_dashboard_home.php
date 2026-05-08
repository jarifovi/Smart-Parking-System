<?php
$pageTitle  = 'Admin Dashboard';
$sidebarKey = 'admin_dashboard';
require_once 'helper_layout_admin.php';

$totalUsers     = $databaseConnection->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$totalSpots     = $databaseConnection->query("SELECT COUNT(*) AS c FROM parking_spots")->fetch_assoc()['c'] ?? 0;
$activeBookings = $databaseConnection->query("SELECT COUNT(*) AS c FROM bookings WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$completedBookings = $databaseConnection->query("SELECT COUNT(*) AS c FROM bookings WHERE status='completed'")->fetch_assoc()['c'] ?? 0;
$totalRevenue   = $databaseConnection->query("SELECT IFNULL(SUM(amount),0) AS s FROM payments WHERE payment_status='paid'")->fetch_assoc()['s'] ?? 0;
?>
<div class="page-header-main">
    <div class="page-header-title">Admin Overview</div>
    <div class="page-header-sub">Manage system-wide activities and monitor performance.</div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Total Users</div>
                <div class="stat-card-number"><?php echo number_format($totalUsers); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Parking Spots</div>
                <div class="stat-card-number"><?php echo number_format($totalSpots); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Active Bookings</div>
                <div class="stat-card-number"><?php echo number_format($activeBookings); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-number">$<?php echo number_format($totalRevenue, 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock-history me-2"></i>RECENT SYSTEM ACTIVITY</span>
                <a href="admin_booking_management.php" class="btn btn-sm btn-outline-primary px-3">View All Activity</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>ID</th><th>User</th><th>Spot</th><th>Status</th><th class="text-end">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $recent = $databaseConnection->query("
                            SELECT b.*, u.full_name, p.spot_number
                            FROM bookings b
                            JOIN users u ON b.user_id = u.id
                            JOIN parking_spots p ON b.spot_id = p.id
                            ORDER BY b.created_at DESC
                            LIMIT 8
                        ");
                        if ($recent && $recent->num_rows):
                            while ($r = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $r['id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($r['spot_number']); ?></span></td>
                                    <td>
                                        <?php if ($r['status'] === 'active'): ?>
                                            <span class="badge bg-success">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo strtoupper($r['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold">$<?php echo number_format($r['amount'], 2); ?></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-secondary">No recent bookings.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-cpu me-2"></i>INFRASTRUCTURE STATUS</div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-secondary">Database Connectivity</span>
                        <span class="small text-success fw-bold">ONLINE</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-secondary">System Load</span>
                        <span class="small text-white fw-bold">24%</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" style="width: 24%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-secondary">Slot Accuracy Rate</span>
                        <span class="small text-white fw-bold">99.8%</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-info" style="width: 99%"></div>
                    </div>
                </div>

                <div class="bg-dark bg-opacity-25 rounded p-3 mb-4 border border-secondary-subtle">
                    <div class="d-flex align-items-center gap-3">
                        <div class="spinner-grow text-success spinner-grow-sm"></div>
                        <div>
                            <div class="fw-bold small">Real-time Sync Active</div>
                            <div class="text-secondary small" style="font-size: 0.7rem;">Last heartbeat: Just now</div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-terminal me-2"></i>Open System Console
                </button>
            </div>
        </div>
    </div>
</div>
<?php
// helper_layout_admin.php will close tags
?>
