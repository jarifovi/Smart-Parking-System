<?php
$pageTitle  = 'Zone Management';
$sidebarKey = 'admin_spots';
require_once 'helper_layout_admin.php';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $databaseConnection->prepare("DELETE FROM parking_spots WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$result = $databaseConnection->query("SELECT * FROM parking_spots ORDER BY floor_number, spot_number");
?>

<div class="page-header-main mb-5">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
                <i class="bi bi-geo-fill text-primary fs-3"></i>
            </div>
            <div>
                <h2 class="fw-800 text-white m-0">Zone Management</h2>
                <p class="text-secondary m-0">Architectural control and node configuration for the entire facility.</p>
            </div>
        </div>
        <a href="admin_parking_spot_create.php" class="btn-primary text-decoration-none">
            <i class="bi bi-plus-circle me-2"></i> ADD NEW NODE
        </a>
    </div>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4"><i class="bi bi-check-circle me-2"></i>New node synchronized successfully.</div>
<?php endif; ?>

<div class="card p-0 overflow-hidden border-primary border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50">NODE ID</th>
                    <th class="small opacity-50">LABEL</th>
                    <th class="small opacity-50">LOCATION</th>
                    <th class="small opacity-50">CATEGORY</th>
                    <th class="small opacity-50">TARIFF</th>
                    <th class="small opacity-50 text-center">STATUS</th>
                    <th class="text-end pe-4 small opacity-50">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): while ($row = $result->fetch_assoc()): ?>
                    <tr class="align-middle">
                        <td class="ps-4 py-4"><code>#<?php echo $row['id']; ?></code></td>
                        <td><span class="fw-bold text-white"><?php echo htmlspecialchars($row['spot_number']); ?></span></td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3">LEVEL <?php echo $row['floor_number']; ?></span></td>
                        <td><span class="text-secondary"><?php echo htmlspecialchars($row['spot_type']); ?></span></td>
                        <td><span class="fw-bold text-success">$<?php echo number_format($row['hourly_rate'], 2); ?>/hr</span></td>
                        <td class="text-center">
                            <?php if ($row['is_active']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">OPERATIONAL</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">OFFLINE</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <a href="admin_parking_spot_management.php?delete=<?php echo $row['id']; ?>&deleted=1"
                               class="btn btn-sm btn-outline-danger border-opacity-25"
                               onclick="return confirm('Permanently disconnect this parking node?');">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center py-5">No parking nodes detected in the zone registry.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'helper_layout_footer.php'; ?>
