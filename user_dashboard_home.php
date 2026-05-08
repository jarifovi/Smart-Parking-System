<?php
// user_dashboard_home.php
$pageTitle  = 'User Dashboard';
$sidebarKey = 'user_dashboard';

// TEMP: enable errors so we can see problems if any
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'helper_layout_user.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// ---------- SAFE STATS QUERIES ----------
$totalSpots = 0;
$activeBookings = 0;
$availableSpots = 0;
$completedCount = 0;
$totalSpent = 0.0;

// total spots
if ($res = $databaseConnection->query("SELECT COUNT(*) AS c FROM parking_spots WHERE is_active = 1")) {
    if ($row = $res->fetch_assoc()) {
        $totalSpots = (int)$row['c'];
    }
}

// active bookings for this user
if ($res = $databaseConnection->query("SELECT COUNT(*) AS c FROM bookings WHERE user_id = {$userId} AND status = 'active'")) {
    if ($row = $res->fetch_assoc()) {
        $activeBookings = (int)$row['c'];
    }
}

// completed bookings
if ($res = $databaseConnection->query("SELECT COUNT(*) AS c FROM bookings WHERE user_id = {$userId} AND status = 'completed'")) {
    if ($row = $res->fetch_assoc()) {
        $completedCount = (int)$row['c'];
    }
}

// total spent
if ($res = $databaseConnection->query("SELECT IFNULL(SUM(amount),0) AS s FROM payments WHERE user_id = {$userId} AND payment_status = 'paid'")) {
    if ($row = $res->fetch_assoc()) {
        $totalSpent = (float)$row['s'];
    }
}

$availableSpots = max(0, $totalSpots - $activeBookings);
?>

<div class="page-header-main">
    <div>
        <div class="page-header-title">
            Welcome back, <?php echo htmlspecialchars($loggedUser['full_name'] ?? 'User'); ?> 👋
        </div>
        <div class="page-header-sub">
            Here’s a quick overview of your parking activity.
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Available Spots</div>
                <div class="stat-card-number"><?php echo number_format($availableSpots); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body border-start border-primary border-4">
                <div class="stat-card-label">Active Bookings</div>
                <div class="stat-card-number text-primary"><?php echo number_format($activeBookings); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Completed</div>
                <div class="stat-card-number"><?php echo number_format($completedCount); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-card-label">Total Spent</div>
                <div class="stat-card-number">$<?php echo number_format($totalSpent, 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-clock-history me-2"></i>ACTIVE BOOKINGS</span>
        <a href="user_bookings_list.php" class="btn btn-sm btn-outline-secondary">History</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Spot</th>
                        <th>Vehicle</th>
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "
                    SELECT b.*, p.spot_number 
                    FROM bookings b 
                    JOIN parking_spots p ON b.spot_id = p.id
                    WHERE b.user_id = {$userId} AND b.status = 'active'
                    ORDER BY b.start_time DESC
                ";
                if ($result = $databaseConnection->query($sql)) {
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($row['spot_number']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['vehicle_label']); ?></td>
                                <td class="small"><?php echo date('M d, H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time'])); ?></td>
                                <td class="fw-bold">$<?php echo number_format($row['amount'], 2); ?></td>
                                <td><span class="badge bg-success">ACTIVE</span></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-calendar-x text-secondary fs-2 mb-2 d-block"></i>
                                <div class="text-secondary">No active bookings found.</div>
                                <a href="user_booking_new.php" class="btn btn-sm btn-primary mt-3">Book Now</a>
                            </td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// helper_layout_user.php will close tags
?>
