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
        <!-- Analytics Chart -->
        <div class="card mb-4 border-info border-opacity-10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-graph-up-arrow me-2 text-info"></i>NETWORK TRAFFIC & REVENUE</span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">Last 7 Days</button>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

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
                            LIMIT 5
                        ");
                        if ($recent && $recent->num_rows):
                            while ($r = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $r['id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?php echo htmlspecialchars($r['spot_number']); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> bg-opacity-10 text-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> border border-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> border-opacity-25">
                                            <?php echo strtoupper($r['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-white">$<?php echo number_format($r['amount'], 2); ?></td>
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
        <div class="card h-100 border-info border-opacity-10">
            <div class="card-header"><i class="bi bi-cpu me-2 text-info"></i>INFRASTRUCTURE STATUS</div>
            <div class="card-body">
                <!-- Status bars remain the same but with better colors -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-secondary">Database Connectivity</span>
                        <span class="small text-success fw-bold">ONLINE</span>
                    </div>
                    <div class="progress bg-dark" style="height: 4px;">
                        <div class="progress-bar bg-success shadow-sm" style="width: 100%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-secondary">CPU Node Load</span>
                        <span class="small text-white fw-bold">24.8%</span>
                    </div>
                    <div class="progress bg-dark" style="height: 4px;">
                        <div class="progress-bar bg-info" style="width: 24.8%"></div>
                    </div>
                </div>

                <div class="bg-dark bg-opacity-25 rounded-4 p-3 mb-4 border border-secondary border-opacity-10">
                    <div class="d-flex align-items-center gap-3">
                        <div class="spinner-grow text-success spinner-grow-sm"></div>
                        <div>
                            <div class="fw-bold small text-white">Cloud Sync Active</div>
                            <div class="text-secondary small" style="font-size: 0.7rem;">Last heartbeat: 0.2ms ago</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info btn-sm py-2">
                        <i class="bi bi-terminal me-2"></i>Access Core Console
                    </button>
                    <button class="btn btn-outline-secondary btn-sm py-2 border-opacity-25">
                        <i class="bi bi-cloud-arrow-down me-2"></i>Download Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(56, 189, 248, 0.4)');
gradient.addColorStop(1, 'rgba(56, 189, 248, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Network Revenue ($)',
            data: [120, 190, 150, 280, 220, 400, 350],
            borderColor: '#38bdf8',
            borderWidth: 3,
            fill: true,
            backgroundColor: gradient,
            tension: 0.4,
            pointBackgroundColor: '#38bdf8',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
        }
    }
});
</script>
<?php
require_once 'helper_layout_footer.php';
?>
<script>
// AI Voice Greeting
document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('greeted')) {
        const msg = new SpeechSynthesisUtterance();
        msg.text = "Welcome back, Operator. System status is synchronized and stable.";
        msg.rate = 0.9;
        msg.pitch = 0.8;
        window.speechSynthesis.speak(msg);
        sessionStorage.setItem('greeted', 'true');
    }
});
</script>
