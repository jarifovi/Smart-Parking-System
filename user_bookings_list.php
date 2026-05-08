<?php
// user_bookings_list.php
$pageTitle  = 'My Bookings';
$sidebarKey = 'user_bookings';

require_once 'helper_layout_user.php'; // includes requireLogin()

$userId = $_SESSION['user_id'] ?? 0;

// Which tab is active? all | active | completed | cancelled
$allowedTabs = ['all', 'active', 'completed', 'cancelled'];
$activeTab   = $_GET['tab'] ?? 'all';
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'all';
}

// Build WHERE clause
$where = 'b.user_id = ?';
$params = [$userId];
$types  = 'i';

if ($activeTab !== 'all') {
    $where .= ' AND b.status = ?';
    $params[] = $activeTab;
    $types   .= 's';
}

// Query bookings + spot number + whether it has a PAID payment
$sql = "
    SELECT 
        b.*,
        ps.spot_number,
        CASE WHEN EXISTS (
            SELECT 1 
            FROM payments p
            WHERE p.booking_id = b.id 
              AND p.payment_status = 'paid'
        ) THEN 1 ELSE 0 END AS has_paid
    FROM bookings b
    JOIN parking_spots ps ON ps.id = b.spot_id
    WHERE $where
    ORDER BY b.id DESC
";

$stmt = $databaseConnection->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-header-main d-flex justify-content-between align-items-center">
    <div>
        <div class="page-header-title">My Booking History</div>
        <div class="page-header-sub">View and manage your current and past parking reservations.</div>
    </div>
    <a href="user_booking_new.php" class="btn btn-primary">
        <i class="bi bi-calendar-plus me-2"></i>New Booking
    </a>
</div>

<!-- Tabs / Filters -->
<div class="card mb-4">
    <div class="card-body p-2">
        <div class="nav nav-pills nav-fill">
            <a href="?tab=all" class="nav-link <?php echo $activeTab === 'all' ? 'active' : ''; ?>">
                <i class="bi bi-list-task me-1"></i> All
            </a>
            <a href="?tab=active" class="nav-link <?php echo $activeTab === 'active' ? 'active' : ''; ?>">
                <i class="bi bi-play-circle me-1"></i> Active
            </a>
            <a href="?tab=completed" class="nav-link <?php echo $activeTab === 'completed' ? 'active' : ''; ?>">
                <i class="bi bi-check-all me-1"></i> Completed
            </a>
            <a href="?tab=cancelled" class="nav-link <?php echo $activeTab === 'cancelled' ? 'active' : ''; ?>">
                <i class="bi bi-x-circle me-1"></i> Cancelled
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Spot</th>
                    <th>Vehicle</th>
                    <th>Time Range</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $status = $row['status'];
                        $badgeClass = 'bg-secondary';
                        if ($status === 'active')     $badgeClass = 'bg-success';
                        if ($status === 'completed')  $badgeClass = 'bg-primary';
                        if ($status === 'cancelled')  $badgeClass = 'bg-danger';

                        $hasPaid   = (int)$row['has_paid'] === 1;
                        $canPayNow = ($status === 'active' && !$hasPaid);
                        ?>
                        <tr>
                            <td>#<?php echo (int)$row['id']; ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($row['spot_number']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['vehicle_label']); ?></td>
                            <td class="small">
                                <div><?php echo date('M d, H:i', strtotime($row['start_time'])); ?></div>
                                <div class="opacity-50"><?php echo date('M d, H:i', strtotime($row['end_time'])); ?></div>
                            </td>
                            <td class="fw-bold">$<?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper($status); ?></span>
                                <?php if ($hasPaid): ?>
                                    <span class="badge bg-opacity-10 bg-success text-success border border-success border-opacity-25 ms-1">PAID</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($canPayNow): ?>
                                    <a href="user_payment_gateway_mock.php?booking_id=<?php echo (int)$row['id']; ?>"
                                       class="btn btn-sm btn-success px-3">
                                        Pay Now
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-calendar2-x text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No records found for the selected filter.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// helper_layout_user.php will close tags
?>
