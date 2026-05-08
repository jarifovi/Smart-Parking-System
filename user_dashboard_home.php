<?php
$pageTitle  = 'My Command Center';
$sidebarKey = 'user_dashboard';
require_once 'helper_layout_user.php';

$userId = (int)$_SESSION['user_id'];
$stats = $databaseConnection->query("
    SELECT 
        (SELECT COUNT(*) FROM bookings WHERE user_id = $userId) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status = 'active') as active_bookings,
        (SELECT IFNULL(SUM(amount),0) FROM payments WHERE user_id = $userId) as total_spent
")->fetch_assoc();
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-info bg-opacity-10 rounded-4 border border-info border-opacity-25">
            <i class="bi bi-person-workspace text-info fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Welcome Back, <?php echo explode(' ', $loggedUser['full_name'])[0]; ?></h2>
            <p class="text-secondary m-0">Your parking network is active and healthy.</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="stat-card-label">Registered Sessions</div>
            <div class="stat-card-number"><?php echo $stats['total_bookings']; ?></div>
            <div class="small text-secondary mt-2">Lifetime reservations</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-info border-opacity-20" style="background: rgba(56, 189, 248, 0.05);">
            <div class="stat-card-label text-info">Current Active</div>
            <div class="stat-card-number"><?php echo $stats['active_bookings']; ?></div>
            <div class="small text-secondary mt-2">Live sessions at terminal</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="stat-card-label">Total Investment</div>
            <div class="stat-card-number">$<?php echo number_format($stats['total_spent'], 2); ?></div>
            <div class="small text-secondary mt-2">Aggregated payment volume</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-0 overflow-hidden">
            <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold"><i class="bi bi-activity me-2 text-info"></i>LIVE RECENT ACTIVITY</h5>
                <a href="user_bookings_list.php" class="btn btn-sm btn-outline-info border-opacity-25 px-3">Sync All Data</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead class="bg-dark bg-opacity-50">
                        <tr>
                            <th class="border-0 ps-4 small">UID</th>
                            <th class="border-0 small">ZONE / SPOT</th>
                            <th class="border-0 small">TIMELINE</th>
                            <th class="border-0 small text-end pe-4">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php
                        $recent = $databaseConnection->query("
                            SELECT b.*, p.spot_number 
                            FROM bookings b 
                            JOIN parking_spots p ON b.spot_id = p.id 
                            WHERE b.user_id = $userId 
                            ORDER BY b.created_at DESC LIMIT 5
                        ");
                        if ($recent && $recent->num_rows):
                            while ($r = $recent->fetch_assoc()): ?>
                            <tr class="align-middle">
                                <td class="ps-4 py-3"><code class="text-info">#<?php echo $r['id']; ?></code></td>
                                <td>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($r['spot_number']); ?></div>
                                    <div class="text-secondary small">Main Terminal Zone</div>
                                </td>
                                <td class="small text-secondary">
                                    <?php echo date('M d, H:i', strtotime($r['start_time'])); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> bg-opacity-10 text-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> border border-<?php echo $r['status'] === 'active' ? 'success' : 'secondary'; ?> border-opacity-25">
                                        <?php echo strtoupper($r['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="4" class="text-center py-5 text-secondary">No telemetry data detected.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Smart Weather Card -->
        <div class="card border-warning border-opacity-20 mb-4" style="background: rgba(245, 158, 11, 0.05);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small fw-bold text-warning"><i class="bi bi-cloud-sun me-2"></i>ENV-INTEL</span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">RAIN DETECTED</span>
            </div>
            <h6 class="text-white fw-bold">Smart Recommendation</h6>
            <p class="text-secondary small mb-3">Heavy rain detected at terminal. We recommend booking **Zone B (Covered)** for vehicle protection.</p>
            <div class="progress bg-dark mb-3" style="height: 4px;">
                <div class="progress-bar bg-warning" style="width: 75%"></div>
            </div>
            <a href="user_booking_new.php" class="btn btn-sm btn-warning bg-opacity-10 text-warning border-warning border-opacity-25 w-100">GO TO COVERED ZONE</a>
        </div>

        <div class="card border-primary border-opacity-10 h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>QUICK ACTIONS</h5>
            <div class="d-grid gap-3">
                <a href="user_booking_new.php" class="btn-primary text-center text-decoration-none">
                    <i class="bi bi-plus-circle me-2"></i> RESERVE NEW SPOT
                </a>
                <a href="user_vehicles.php" class="btn btn-outline-info w-100 py-3 border-opacity-25">
                    <i class="bi bi-car-front me-2"></i> MANAGE MY FLEET
                </a>
                <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary border-opacity-10 mt-2">
                    <div class="small text-secondary mb-2">NETWORK STATUS</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="spinner-grow spinner-grow-sm text-success"></span>
                        <span class="text-success small fw-bold">LATENCY: 12ms</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// AI Voice Greeting for User
document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('user_greeted')) {
        const msg = new SpeechSynthesisUtterance();
        msg.text = "Portal access granted. Welcome to your command center, <?php echo explode(' ', $loggedUser['full_name'])[0]; ?>. Zone B is now available for covered parking.";
        msg.rate = 1.0;
        msg.pitch = 0.9;
        window.speechSynthesis.speak(msg);
        sessionStorage.setItem('user_greeted', 'true');
    }
});
</script>
<?php
require_once 'helper_layout_footer.php';
?>
