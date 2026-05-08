<?php
require_once 'config_database.php';
require_once 'helper_authentication.php';
requireAdmin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spotNumber = trim($_POST['spot_number'] ?? '');
    $floor      = (int)($_POST['floor_number'] ?? 0);
    $spotType   = $_POST['spot_type'] ?? 'Car';
    $rate       = (float)($_POST['hourly_rate'] ?? 0);
    $isActive   = isset($_POST['is_active']) ? 1 : 0;

    if ($spotNumber === '') { $errors[] = 'Spot identity label is required.'; }
    if ($rate <= 0) { $errors[] = 'Hourly tariff must be greater than zero.'; }

    if (!$errors) {
        $stmt = $databaseConnection->prepare("INSERT INTO parking_spots (spot_number, floor_number, spot_type, hourly_rate, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sisdi', $spotNumber, $floor, $spotType, $rate, $isActive);
        if ($stmt->execute()) {
            header('Location: admin_parking_spot_management.php?created=1');
            exit;
        } else {
            $errors[] = 'Conflict detected: This node identity already exists in the registry.';
        }
    }
}

$pageTitle  = 'Node Provisioning';
$sidebarKey = 'admin_spots';
require_once 'helper_layout_admin.php';
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-plus-lg text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Node Provisioning</h2>
            <p class="text-secondary m-0">Initialize and configure new physical parking nodes in the system registry.</p>
        </div>
    </div>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4"><i class="bi bi-exclamation-octagon me-2"></i><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="post" class="card border-primary border-opacity-10">
            <div class="card-body">
                <h5 class="text-white opacity-75 mb-4 fw-bold">NODE PARAMETERS</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">SPOT IDENTITY <span class="text-danger">*</span></label>
                        <input type="text" name="spot_number" class="form-control" placeholder="e.g. A-101" required value="<?php echo htmlspecialchars($_POST['spot_number'] ?? ''); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">FLOOR/LEVEL</label>
                        <input type="number" name="floor_number" class="form-control" value="<?php echo htmlspecialchars($_POST['floor_number'] ?? '0'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">VEHICLE CLASS</label>
                        <select name="spot_type" class="form-control">
                            <?php foreach (['Car', 'Bike', 'VIP'] as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo ($_POST['spot_type'] ?? 'Car') === $t ? 'selected' : ''; ?>><?php echo $t; ?> Terminal</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold">HOURLY TARIFF ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control" required value="<?php echo htmlspecialchars($_POST['hourly_rate'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="mt-4 p-4 bg-primary bg-opacity-5 rounded-4 border border-primary border-opacity-10">
                    <div class="form-check form-switch d-flex align-items-center gap-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                        <label for="is_active" class="form-check-label text-white fw-bold">INITIALIZE AS OPERATIONAL</label>
                    </div>
                </div>

                <div class="mt-5 d-flex justify-content-between align-items-center">
                    <a href="admin_parking_spot_management.php" class="text-secondary text-decoration-none small fw-bold hover-info">
                        <i class="bi bi-arrow-left me-1"></i> BACK TO REGISTRY
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save2 me-2"></i> COMMIT NODE
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card h-100 border-info border-opacity-10 bg-opacity-5">
            <div class="card-body p-4">
                <i class="bi bi-info-circle fs-2 text-info mb-3"></i>
                <h5 class="text-white fw-bold">Provisioning Logic</h5>
                <p class="text-secondary small">Newly provisioned nodes are automatically synchronized with the global user portal and mapping engines.</p>
                <hr class="border-secondary border-opacity-10">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-2 small text-secondary">
                        <i class="bi bi-qr-code text-success"></i> Auto-generates node QR
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-secondary">
                        <i class="bi bi-shield-check text-success"></i> Security protocols active
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-secondary">
                        <i class="bi bi-broadcast text-success"></i> Live telemetry enabled
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'helper_layout_footer.php'; ?>
