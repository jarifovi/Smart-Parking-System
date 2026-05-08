<?php
// admin_parking_spot_management.php
$pageTitle  = 'Parking Spots Management';
$sidebarKey = 'admin_spots';

require_once 'helper_layout_admin.php';   // gives $databaseConnection

// Optional: handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $databaseConnection->prepare("DELETE FROM parking_spots WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Load all spots
$result = $databaseConnection->query("
    SELECT * FROM parking_spots
    ORDER BY floor_number, spot_number
");
?>

<div class="page-header-main d-flex justify-content-between align-items-center">
    <div>
        <div class="page-header-title">Parking Spots Management</div>
        <div class="page-header-sub">Configure and monitor individual parking slots across all levels.</div>
    </div>
    <a href="admin_parking_spot_create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add New Spot
    </a>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i>New parking spot added successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>Parking spot removed from system.</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Spot Label</th>
                    <th>Floor</th>
                    <th>Category</th>
                    <th>Hourly Rate</th>
                    <th>Operational Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo (int)$row['id']; ?></td>
                            <td><span class="fw-bold"><?php echo htmlspecialchars($row['spot_number']); ?></span></td>
                            <td><span class="badge bg-opacity-10 bg-info text-info border border-info border-opacity-25">Level <?php echo (int)$row['floor_number']; ?></span></td>
                            <td><?php echo htmlspecialchars($row['spot_type']); ?></td>
                            <td><span class="text-success fw-bold">$<?php echo number_format($row['hourly_rate'], 2); ?></span></td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <span class="badge bg-success">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">DISABLED</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="admin_parking_spot_management.php?delete=<?php echo (int)$row['id']; ?>&deleted=1"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Permanently delete this parking spot?');"
                                   title="Delete Spot">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-parking text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No parking spots found in database.</div>
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
