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
    <!-- Sentinel Drone Hub -->
    <div class="col-lg-4">
        <div class="card h-100 border-warning border-opacity-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-rocket-takeoff-fill text-warning me-2"></i>DRONE HUB</h5>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 x-small fw-bold">DRONE-01: PATROLLING</span>
            </div>
            <div class="bg-dark rounded-4 p-3 mb-4 text-center position-relative overflow-hidden" style="height: 180px; border: 1px solid rgba(251, 191, 36, 0.1);">
                <i class="bi bi-radar text-warning opacity-10" style="font-size: 8rem; position: absolute; top: 10px; left: 50%; transform: translateX(-50%);"></i>
                <div class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-grow text-warning" style="width: 3rem; height: 3rem;"></div>
                </div>
                <div class="position-absolute bottom-0 start-0 w-100 p-2 x-small text-secondary fw-bold">SECTOR ALPHA RADAR ACTIVE</div>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary small fw-bold">POWER CORE</span>
                <span class="text-warning fw-900">92%</span>
            </div>
            <div class="vanguard-progress mb-3">
                <div class="vanguard-progress-bar bg-warning" style="width: 92%; box-shadow: 0 0 10px #fbbf24;"></div>
            </div>
            <p class="x-small text-secondary m-0">Encryption: <span class="text-white">AES-256 BIT</span> | Status: <span class="text-success">SECURE</span></p>
        </div>
    </div>

    <!-- Surveillance Feed Node (Shifted) -->
    <div class="col-lg-8">
        <div class="card border-info border-opacity-10 p-0 overflow-hidden" style="height: 350px; background: #000;">
            <div class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between align-items-start z-1">
                <div>
                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 x-small fw-bold animate-pulse">
                        <i class="bi bi-record-circle-fill me-1"></i> LIVE FEED: CAM 04
                    </span>
                    <div class="x-small text-white opacity-50 fw-bold mt-1">MAIN ENTRANCE / SECTOR G</div>
                </div>
                <div class="text-end x-small text-white opacity-50 fw-bold">
                    [MOTION DETECTED] <br>
                    <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            <img src="sentinel_surveillance_node_1778273996744.png" class="w-100 h-100 object-fit-cover opacity-75" style="filter: grayscale(1) contrast(1.2);">
            <div class="position-absolute top-0 left-0 w-100 h-100 pointer-events-none" style="background: repeating-linear-gradient(rgba(0,0,0,0) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0) 100%); background-size: 100% 4px;"></div>
        </div>
    </div>
</div>
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
            
            <!-- Adaptive Pricing Node -->
            <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-10 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small fw-bold text-white">ADAPTIVE PRICING</div>
                    <span class="badge bg-success bg-opacity-10 text-success x-small">+12% SURGE</span>
                </div>
                <div class="x-small text-secondary mt-1">High demand due to <i class="bi bi-cloud-rain me-1"></i> Precipitation.</div>
            </div>

            <!-- Gate Telemetry -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-secondary fw-bold">GATE NODE 01</span>
                    <span class="badge bg-success bg-opacity-10 text-success x-small">SECURED</span>
                </div>
                <div class="progress bg-dark" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-secondary fw-bold">GATE NODE 02</span>
                    <span class="badge bg-info bg-opacity-10 text-info x-small" id="gatePulse">IDLE</span>
                </div>
                <div class="progress bg-dark" style="height: 4px;">
                    <div class="progress-bar bg-info" id="gateProgress" style="width: 10%"></div>
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

            <!-- Lockdown Protocol Node -->
            <div class="p-3 bg-danger bg-opacity-5 rounded-4 border border-danger border-opacity-20 mb-4 text-center">
                <div class="x-small fw-bold text-danger mb-2">SECURITY OVERRIDE</div>
                <button class="btn btn-sm btn-outline-danger w-100 py-3 fw-900" id="lockdownBtn" onclick="initiateLockdown()">
                    <i class="bi bi-shield-lock-fill me-2"></i> GLOBAL LOCKDOWN
                </button>
            </div>

            <button class="btn btn-outline-info w-100 mb-2 py-2 border-opacity-25 small fw-bold">ACCESS CORE CONSOLE</button>
        </div>
    </div>
</div>

<!-- Maintenance Forecast Hub -->
<div class="row g-4 mt-2 mb-4">
    <div class="col-lg-12">
        <div class="card border-warning border-opacity-10 bg-opacity-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-wrench-adjustable text-warning me-2"></i>PREDICTIVE REPAIR</h5>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 x-small fw-bold">7 NODES WATCHED</span>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="x-small text-secondary fw-bold">GATE-01 HYDRAULICS</span>
                        <span class="text-warning x-small fw-bold">94% WEAR</span>
                    </div>
                    <div class="vanguard-progress" style="height: 4px;">
                        <div class="vanguard-progress-bar bg-danger" style="width: 94%;"></div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="x-small text-secondary fw-bold">NODE-42 OPTICS</span>
                        <span class="text-info x-small fw-bold">12% WEAR</span>
                    </div>
                    <div class="vanguard-progress" style="height: 4px;">
                        <div class="vanguard-progress-bar bg-info" style="width: 12%;"></div>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-warning w-100 x-small fw-bold py-2 mt-2" onclick="playSFX('hydraulic')">INITIALIZE PREVENTATIVE REPAIR</button>
        </div>
    <!-- Valet-Bot Telemetry Node -->
    <div class="col-lg-4">
        <div class="card h-100 border-primary border-opacity-10 bg-opacity-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-cpu-fill text-primary me-2"></i>VALET-BOT 07</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 x-small fw-bold">ACTIVE: LEVEL 2</span>
            </div>
            <div class="text-center py-4 mb-3">
                <i class="bi bi-box-seam-fill text-primary opacity-25 animate-bounce" style="font-size: 4rem;"></i>
                <div class="mt-2 x-small text-secondary fw-bold">CARGO ID: DHAKA-1234</div>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary small fw-bold">BATTERY HUB</span>
                <span class="text-primary fw-900">78%</span>
            </div>
            <div class="vanguard-progress mb-4">
                <div class="vanguard-progress-bar bg-primary" style="width: 78%; box-shadow: 0 0 10px var(--accent-main);"></div>
            </div>
            <p class="x-small text-secondary m-0">Destination: <span class="text-white">SECTION G-12</span> | ETA: <span class="text-primary">42s</span></p>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Neural Topology Map -->
    <div class="col-lg-8">
        <div class="card h-100 border-primary border-opacity-10 bg-opacity-5 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-diagram-3-fill text-primary me-2"></i>SYSTEM MIND MAP</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 x-small fw-bold">NETWORK: SYNCHRONIZED</span>
            </div>
            <div class="text-center py-5 position-relative" style="min-height: 250px;">
                <div class="position-absolute top-50 start-50 translate-middle">
                    <div class="d-flex align-items-center gap-5">
                        <div class="text-center">
                            <i class="bi bi-hdd-network-fill text-primary mb-2" style="font-size: 2rem;"></i>
                            <div class="x-small text-secondary fw-bold">CORE</div>
                        </div>
                        <div class="animate-pulse text-primary"><i class="bi bi-arrow-left-right" style="font-size: 2rem;"></i></div>
                        <div class="text-center">
                            <i class="bi bi-robot text-info mb-2" style="font-size: 2rem;"></i>
                            <div class="x-small text-secondary fw-bold">BOTS</div>
                        </div>
                        <div class="animate-pulse text-primary"><i class="bi bi-arrow-left-right" style="font-size: 2rem;"></i></div>
                        <div class="text-center">
                            <i class="bi bi-camera-reels-fill text-warning mb-2" style="font-size: 2rem;"></i>
                            <div class="x-small text-secondary fw-bold">EYES</div>
                        </div>
                    </div>
                </div>
                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
                    <svg width="100%" height="100%" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="var(--accent-main)" stroke-width="0.5" stroke-dasharray="2,2"></circle>
                    </svg>
                </div>
            </div>
            <div class="mt-auto p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
                <div class="x-small text-secondary fw-bold">LATENCY: <span class="text-primary">0.002ms</span> | PACKET LOSS: <span class="text-success">0%</span></div>
            </div>
        </div>
    </div>

    <!-- Atmospheric Purity Node -->
    <div class="col-lg-4">
        <div class="card h-100 border-info border-opacity-10 bg-opacity-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-wind text-info me-2"></i>ATMOSPHERE</h5>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 x-small fw-bold">PURITY: 99.8%</span>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary x-small fw-bold">OXYGEN CONTENT</span>
                    <span class="text-info small fw-900">20.9%</span>
                </div>
                <div class="vanguard-progress" style="height: 4px;">
                    <div class="vanguard-progress-bar bg-info" style="width: 100%;"></div>
                </div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary x-small fw-bold">CO2 CONCENTRATION</span>
                    <span class="text-warning small fw-900">412 PPM</span>
                </div>
                <div class="vanguard-progress" style="height: 4px;">
                    <div class="vanguard-progress-bar bg-warning" style="width: 45%;"></div>
                </div>
            </div>
            <p class="x-small text-secondary m-0">Filtration Node <span class="text-info fw-bold">SECTOR-A</span> active and cycling.</p>
        </div>
    </div>
</div>

<!-- Aurora Energy Node -->
<div class="card energy-card border-primary border-opacity-10 mb-4">
    <div class="row g-0 align-items-center p-3">
        <div class="col-md-3 border-end border-secondary border-opacity-10">
            <h5 class="text-white fw-bold m-0"><i class="bi bi-lightning-charge-fill solar-glow me-2"></i>AURORA CORE</h5>
            <p class="x-small text-secondary m-0">Facility Power Balance</p>
        </div>
        <div class="col-md-4 px-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="x-small text-secondary fw-bold">SOLAR HARVEST</span>
                <span class="solar-glow x-small fw-900">12.4 kW</span>
            </div>
            <div class="vanguard-progress" style="height: 4px;">
                <div class="vanguard-progress-bar" style="width: 65%; background: #fbbf24;"></div>
            </div>
        </div>
        <div class="col-md-4 px-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="x-small text-secondary fw-bold">FACILITY GRID</span>
                <span class="grid-glow x-small fw-900">4.2 kW</span>
            </div>
            <div class="vanguard-progress" style="height: 4px;">
                <div class="vanguard-progress-bar" style="width: 25%; background: #38bdf8;"></div>
            </div>
        </div>
        <div class="col-md-1 text-end">
            <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 x-small fw-bold">ACTIVE</div>
        </div>
    </div>
</div>

<!-- PREDICTIVE ANALYTICS SECTION -->
<div class="row g-4 mt-2 mb-5">
    <div class="col-lg-8">
        <div class="card border-primary border-opacity-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="text-white fw-bold m-0">PREDICTIVE OCCUPANCY</h5>
                    <p class="x-small text-secondary m-0">Neural forecast for the next 24 hours.</p>
                </div>
                <div class="badge bg-primary bg-opacity-10 text-info border border-info border-opacity-25">AI ACTIVE</div>
            </div>
            <div style="height: 250px;">
                <canvas id="predictiveChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 border-info border-opacity-10 bg-opacity-5">
            <h5 class="text-white fw-bold mb-4">SYSTEM LOGS</h5>
            <div class="font-monospace x-small text-secondary overflow-auto" style="height: 200px;" id="systemLogs">
                <div>[02:15:01] Neural Engine: SYNCHRONIZED</div>
                <div>[02:15:05] Gate Node 01: HEARTBEAT NOMINAL</div>
                <div>[02:15:10] Predictive Logic: UPDATED TRENDS</div>
            </div>
        </div>
    </div>
</div>

<script>
function initiateLockdown() {
    if(confirm("CRITICAL: Do you wish to activate the Global Lockdown Protocol? This will secure all entry nodes.")) {
        document.body.style.filter = "sepia(1) saturate(2) hue-rotate(-50deg)";
        const btn = document.getElementById('lockdownBtn');
        btn.innerHTML = "<i class='bi bi-shield-fill-exclamation me-2'></i> LOCKDOWN ACTIVE";
        btn.classList.replace('btn-outline-danger', 'btn-danger');
        alert("FACILITY SECURED: All gate nodes have been locked.");
    }
}

// Predictive Chart Logic
const pCtx = document.getElementById('predictiveChart').getContext('2d');
new Chart(pCtx, {
    type: 'line',
    data: {
        labels: ['06:00', '09:00', '12:00', '15:00', '18:00', '21:00', '00:00'],
        datasets: [{
            label: 'FORECASTED LOAD',
            data: [20, 85, 60, 95, 80, 40, 15],
            borderColor: '#fbbf24',
            borderDash: [5, 5],
            backgroundColor: 'transparent',
            tension: 0.4,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { display: false },
            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
        }
    }
});
</script>

<!-- Eco-Impact Node -->
<div class="card eco-card border-emerald border-opacity-10 mb-4">
    <div class="d-flex justify-content-between align-items-center p-3">
        <div>
            <h5 class="text-white fw-bold m-0"><i class="bi bi-tree-fill eco-glow me-2"></i>ECO-NEURAL IMPACT</h5>
            <p class="x-small text-secondary m-0">Facility optimization has saved <span class="eco-glow fw-bold">142kg</span> of CO2 this month.</p>
        </div>
        <div class="text-end">
            <div class="small fw-900 text-white">98.4%</div>
            <div class="x-small text-secondary fw-bold">CLEAN SCORE</div>
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
glowGradient.addColorStop(0, 'rgba(251, 191, 36, 0.4)');
glowGradient.addColorStop(0.5, 'rgba(251, 191, 36, 0.1)');
glowGradient.addColorStop(1, 'rgba(251, 191, 36, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
        datasets: [{
            label: 'REVENUE TELEMETRY',
            data: [1200, 1900, 1500, 2800, 2200, 4000, 3500],
            borderColor: '#fbbf24',
            borderWidth: 4,
            fill: true,
            backgroundColor: glowGradient,
            tension: 0.4,
            pointBackgroundColor: '#000',
            pointBorderColor: '#fbbf24',
            pointBorderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(2, 6, 23, 0.95)',
                borderColor: 'rgba(251, 191, 36, 0.2)',
                borderWidth: 1,
                padding: 15,
                cornerRadius: 12,
                titleFont: { family: 'Outfit', weight: 'bold' },
                bodyFont: { family: 'Outfit', size: 14 },
                callbacks: { label: (item) => `$${item.raw.toLocaleString()}` }
            }
        },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.02)' }, ticks: { color: '#64748b', font: { size: 10 } } },
            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
        }
    }
});
</script>

<?php require_once 'helper_layout_footer.php'; ?>
