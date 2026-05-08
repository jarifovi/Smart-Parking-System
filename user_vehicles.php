<?php
$pageTitle  = 'My Vehicles';
$sidebarKey = 'user_vehicles';
require_once 'helper_layout_user.php';

// Migration: Create table if not exists
$databaseConnection->query("
    CREATE TABLE IF NOT EXISTS `user_vehicles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `vehicle_label` varchar(50) NOT NULL,
        `vehicle_type` enum('Car','Bike','VIP') NOT NULL,
        `is_default` tinyint(1) DEFAULT 0,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$success = "";
$error = "";

// Handle Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vehicle'])) {
        $label = trim($_POST['label'] ?? '');
        $type  = $_POST['type'] ?? 'Car';
        
        if ($label) {
            $stmt = $databaseConnection->prepare("INSERT INTO user_vehicles (user_id, vehicle_label, vehicle_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $loggedUser['id'], $label, $type);
            if ($stmt->execute()) {
                $success = "Vehicle added successfully!";
            }
        }
    }
    
    if (isset($_POST['delete_id'])) {
        $did = (int)$_POST['delete_id'];
        $stmt = $databaseConnection->prepare("DELETE FROM user_vehicles WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $did, $loggedUser['id']);
        $stmt->execute();
        $success = "Vehicle removed.";
    }
}

// Fetch user vehicles
$stmt = $databaseConnection->prepare("SELECT * FROM user_vehicles WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $loggedUser['id']);
$stmt->execute();
$vehicles = $stmt->get_result();
?>

<div class="page-header-main">
    <div class="page-header-title">My Registered Vehicles</div>
    <div class="page-header-sub">Save your vehicle details for faster parking reservations.</div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Register New Vehicle</div>
            <div class="card-body p-4">
                <form method="post">
                    <input type="hidden" name="add_vehicle" value="1">
                    <div class="mb-3">
                        <label class="form-label small">Vehicle Label / Plate</label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. DHAKA-METRO-1234" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small">Vehicle Type</label>
                        <select name="type" class="form-select">
                            <option value="Car">Car</option>
                            <option value="Bike">Bike</option>
                            <option value="VIP">VIP / Luxury</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Vehicle</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-car-front me-2"></i>Saved Vehicles</span>
                <span class="badge bg-secondary"><?php echo $vehicles->num_rows; ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Plate Number</th>
                                <th>Category</th>
                                <th>Date Added</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($vehicles->num_rows > 0): ?>
                                <?php while ($v = $vehicles->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($v['vehicle_label']); ?></td>
                                        <td>
                                            <?php if ($v['vehicle_type'] === 'Car'): ?>
                                                <span class="badge bg-primary"><i class="bi bi-car-front me-1"></i>CAR</span>
                                            <?php elseif ($v['vehicle_type'] === 'Bike'): ?>
                                                <span class="badge bg-info"><i class="bi bi-bicycle me-1"></i>BIKE</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>VIP</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-secondary"><?php echo date('M d, Y', strtotime($v['created_at'])); ?></td>
                                        <td class="text-end">
                                            <form method="post" class="d-inline" onsubmit="return confirm('Remove this vehicle?')">
                                                <input type="hidden" name="delete_id" value="<?php echo $v['id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-car-front text-secondary fs-1 mb-2 d-block"></i>
                                        <div class="text-secondary">No vehicles registered yet.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// helper_layout_user.php will close tags
?>
