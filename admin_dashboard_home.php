<?php
$pageTitle  = 'Command Center';
$sidebarKey = 'admin_dashboard';
require_once 'helper_layout_admin.php';

// Fetch recent activity for the telemetry table
$recentBookings = $databaseConnection->query("
    SELECT b.*, u.full_name, s.spot_number 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_spots s ON b.spot_id = s.id
    ORDER BY b.created_at DESC LIMIT 5
");
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-info bg-opacity-10 rounded-4 border border-info border-opacity-25">
            <i class="bi bi-terminal-fill text-info fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">System Overview</h2>
            <p class="text-secondary m-0">Real-time infrastructure heartbeat and node telemetry.</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Main Telemetry Chart -->
    <div class="col-lg-8">
        <div class="card h-100 border-info border-opacity-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 fw-bold text-white opacity-75">REVENUE STREAM</h5>
                <select class="form-select form-select-sm bg-dark border-secondary border-opacity-25 text-secondary w-auto" style="border-radius: 10px;">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Infrastructure Status -->
    <div class="col-lg-4">
        <div class="card h-100 border-info border-opacity-10">
            <h5 class="mb-4 fw-bold text-white opacity-75"><i class="bi bi-cpu me-2"></i>INFRASTRUCTURE</h5>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-secondary fw-bold">Database Connectivity</span>
                    <span class="small text-success fw-bold">ONLINE</span>
                </div>
                <div class="progress bg-dark" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-secondary fw-bold">CPU Node Load</span>
                    <span class="small text-info fw-bold">24.8%</span>
                </div>
                <div class="progress bg-dark" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 24%"></div>
                </div>
            </div>

            <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary border-opacity-10 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="spinner-grow spinner-grow-sm text-success"></div>
                    <div>
                        <div class="small fw-bold text-white">Cloud Sync Active</div>
                        <div class="x-small text-secondary">Last heartbeat: 0.2ms ago</div>
                    </div>
                </div>
            </div>

            <button class="btn btn-outline-info w-100 mb-2 py-2 border-opacity-25 small fw-bold">ACCESS CORE CONSOLE</button>
            <button class="btn btn-outline-secondary w-100 py-2 border-opacity-25 small fw-bold">DOWNLOAD LOGS</button>
        </div>
    </div>
</div>

<!-- Recent Activity Ledger -->
<div class="card border-info border-opacity-10 p-0 overflow-hidden">
    <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-white opacity-75">RECENT ACTIVITY LEDGER</h5>
        <a href="admin_booking_management.php" class="btn btn-sm btn-outline-info border-opacity-25 px-3">View All Activity</a>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50 py-3">NODE ID</th>
                    <th class="small opacity-50 py-3">USER</th>
                    <th class="small opacity-50 py-3">SPOT</th>
                    <th class="small opacity-50 py-3">STATUS</th>
                    <th class="text-end pe-4 small opacity-50 py-3">GROSS AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php while($b = $recentBookings->fetch_assoc()): ?>
                <tr class="align-middle">
                    <td class="ps-4 py-3"><code>#<?php echo $b['id']; ?></code></td>
                    <td class="fw-bold text-white"><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?php echo htmlspecialchars($b['spot_number']); ?></span></td>
                    <td>
                        <?php $s = strtolower($b['status']); ?>
                        <span class="badge bg-<?php echo $s==='active'?'success':'secondary'; ?> bg-opacity-10 text-<?php echo $s==='active'?'success':'secondary'; ?> border border-<?php echo $s==='active'?'success':'secondary'; ?> border-opacity-25 px-3">
                            <?php echo strtoupper($b['status']); ?>
                        </span>
                    </td>
                    <td class="text-end pe-4 fw-800 text-white">$<?php echo number_format($b['amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const glowGradient = ctx.createLinearGradient(0, 0, 0, 400);
glowGradient.addColorStop(0, 'rgba(129, 140, 248, 0.5)');
glowGradient.addColorStop(0.5, 'rgba(129, 140, 248, 0.1)');
glowGradient.addColorStop(1, 'rgba(129, 140, 248, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
        datasets: [{
            label: 'REVENUE TELEMETRY',
            data: [1200, 1900, 1500, 2800, 2200, 4000, 3500],
            borderColor: '#818cf8',
            borderWidth: 5,
            fill: true,
            backgroundColor: glowGradient,
            tension: 0.5,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#818cf8',
            pointBorderWidth: 3,
            pointRadius: 0,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#818cf8',
            pointHoverBorderWidth: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(2, 6, 23, 0.9)',
                titleFont: { family: 'Outfit', size: 12, weight: 'bold' },
                bodyFont: { family: 'Outfit', size: 16 },
                padding: 15,
                cornerRadius: 15,
                displayColors: false,
                borderColor: 'rgba(129, 140, 248, 0.2)',
                borderWidth: 1,
                callbacks: {
                    label: (item) => `$${item.raw.toLocaleString()}`
                }
            }
        },
        scales: {
            y: { 
                grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }, 
                ticks: { color: '#64748b', font: { family: 'Outfit', size: 10, weight: '600' } } 
            },
            x: { 
                grid: { display: false }, 
                ticks: { color: '#64748b', font: { family: 'Outfit', size: 10, weight: '600' } } 
            }
        }
    }
});
</script>

<?php require_once 'helper_layout_footer.php'; ?>
