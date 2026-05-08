<?php
require_once 'config_database.php';
require_once 'helper_authentication.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];

// --- FORCE SCHEMA REPAIR ---
$tableCheck = $databaseConnection->query("SHOW TABLES LIKE 'user_vehicles'");
if ($tableCheck->num_rows == 0) {
    // Create table if totally missing
    $databaseConnection->query("
        CREATE TABLE IF NOT EXISTS `user_vehicles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `vehicle_label` varchar(100) NOT NULL,
            `plate_number` varchar(50) NOT NULL,
            `vehicle_type` varchar(50) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} else {
    // Check if plate_number column exists, if not, add it or rename 'plate'
    $columns = $databaseConnection->query("SHOW COLUMNS FROM user_vehicles");
    $hasPlateNumber = false;
    $hasPlate = false;
    while($col = $columns->fetch_assoc()) {
        if ($col['Field'] === 'plate_number') $hasPlateNumber = true;
        if ($col['Field'] === 'plate') $hasPlate = true;
    }
    
    if (!$hasPlateNumber) {
        if ($hasPlate) {
            // Rename 'plate' to 'plate_number' for consistency
            $databaseConnection->query("ALTER TABLE user_vehicles CHANGE plate plate_number varchar(50)");
        } else {
            // Add it if missing entirely
            $databaseConnection->query("ALTER TABLE user_vehicles ADD plate_number varchar(50) NOT NULL AFTER vehicle_label");
        }
    }
}

// --- HANDLE REDIRECT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $label = trim($_POST['label'] ?? '');
    $plate = trim($_POST['plate'] ?? '');
    $type  = $_POST['type'] ?? 'Car';

    if ($label && $plate) {
        $stmt = $databaseConnection->prepare("INSERT INTO user_vehicles (user_id, vehicle_label, plate_number, vehicle_type) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $userId, $label, $plate, $type);
            $stmt->execute();
            header("Location: user_vehicles.php?success=1");
            exit;
        } else {
            die("Critical Sync Error: " . $databaseConnection->error);
        }
    }
}

// --- START LAYOUT ---
$pageTitle  = 'My Vehicle Fleet';
$sidebarKey = 'user_vehicles';
require_once 'helper_layout_user.php';

$res = $databaseConnection->query("SELECT * FROM user_vehicles WHERE user_id = $userId ORDER BY created_at DESC");
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-car-front-fill text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Vehicle Management</h2>
            <p class="text-secondary m-0">Manage your digital identity for gate entry.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100 border-primary border-opacity-10">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-plus-circle-dotted me-2 text-primary"></i>Register New Node</h5>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">VEHICLE NAME / LABEL</label>
                    <input type="text" name="label" class="form-control" placeholder="e.g., My Tesla" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">LICENSE PLATE ID</label>
                    <input type="text" name="plate" class="form-control" placeholder="DHAKA-METRO-123" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">VEHICLE TYPE</label>
                    <select name="type" class="form-control">
                        <option value="Car">Sedan / Hatchback</option>
                        <option value="SUV">SUV / Crossover</option>
                        <option value="Motorcycle">Motorcycle / Bike</option>
                        <option value="Truck">Truck / Heavy</option>
                    </select>
                </div>
                <button type="submit" name="add_vehicle" class="btn-primary w-100 py-3">
                    INITIALIZE VEHICLE <i class="bi bi-plus-lg ms-2"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100 p-0 overflow-hidden border-info border-opacity-10">
            <div class="p-4 border-bottom border-secondary border-opacity-10">
                <h5 class="m-0 fw-bold text-white"><i class="bi bi-cpu-fill me-2 text-info"></i>ACTIVE FLEET TELEMETRY</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php if ($res && $res->num_rows): while ($v = $res->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-dark bg-opacity-50 border border-secondary border-opacity-10 hover-border-info transition-smooth">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="p-2 bg-info bg-opacity-10 rounded-3 text-info">
                                        <?php $icon = (stripos($v['vehicle_type'], 'bike') !== false || stripos($v['vehicle_type'], 'motorcycle') !== false) ? 'bicycle' : 'car-front-fill'; ?>
                                        <i class="bi bi-<?php echo $icon; ?> fs-4"></i>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 x-small fw-bold">CONNECTED</span>
                                </div>
                                <h6 class="text-white fw-bold mb-1 h5"><?php echo htmlspecialchars($v['vehicle_label']); ?></h6>
                                <div class="font-monospace text-info mb-4" style="letter-spacing: 2px; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($v['plate_number'] ?? 'UNRECOGNIZED'); ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary border-opacity-25 flex-grow-1 x-small fw-bold">CONFIG</button>
                                    <button class="btn btn-sm btn-outline-danger border-opacity-25 x-small"><i class="bi bi-trash3"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="text-center py-5">
                            <div class="p-4 bg-dark bg-opacity-50 rounded-circle d-inline-flex mb-4 border border-secondary border-opacity-10">
                                <i class="bi bi-broadcast text-secondary fs-1"></i>
                            </div>
                            <div class="text-secondary small fw-bold">No vehicles detected in your network.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'helper_layout_footer.php'; ?>
