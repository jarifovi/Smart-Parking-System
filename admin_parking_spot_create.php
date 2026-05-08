<?php
// admin_parking_spot_create.php

// 1. Do all PHP / DB logic BEFORE any HTML output
require_once 'config_database.php';
require_once 'helper_authentication.php';
requireLogin();    // or requireAdmin() if you have that function

$errors = [];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spotNumber = trim($_POST['spot_number'] ?? '');
    $floor      = (int)($_POST['floor_number'] ?? 0);
    $spotType   = $_POST['spot_type'] ?? 'Car';
    $rate       = (float)($_POST['hourly_rate'] ?? 0);
    $isActive   = isset($_POST['is_active']) ? 1 : 0;

    if ($spotNumber === '') {
        $errors[] = 'Spot number is required.';
    }
    if ($rate <= 0) {
        $errors[] = 'Hourly rate must be greater than 0.';
    }

    if (!$errors) {
        $stmt = $databaseConnection->prepare("
            INSERT INTO parking_spots (spot_number, floor_number, spot_type, hourly_rate, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sisdi', $spotNumber, $floor, $spotType, $rate, $isActive);

        if ($stmt->execute()) {
            // Go back to list page with success flag
            header('Location: admin_parking_spot_management.php?created=1');
            exit;
        } else {
            $errors[] = 'Could not save spot. The spot number may already exist.';
        }
    }
}

// 2. Set page meta + load admin layout (navbar + sidebar + opening <main>)
$pageTitle  = 'Add Parking Spot';
$sidebarKey = 'admin_spots';
require_once 'helper_layout_admin.php';
?>

<div class="page-header-main">
    <div class="page-header-title">Add New Parking Spot</div>
    <div class="page-header-sub">Configure a new parking slot with specific rates and type.</div>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="post" class="card">
            <div class="card-header"><i class="bi bi-plus-square me-2"></i>Spot Configuration</div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Spot Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                <i class="bi bi-hash text-secondary"></i>
                            </span>
                            <input type="text" name="spot_number" class="form-control border-start-0 ps-0" placeholder="e.g. A-101" required value="<?php echo htmlspecialchars($_POST['spot_number'] ?? ''); ?>">
                        </div>
                        <div class="form-text">Unique identifier for this parking location.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Floor Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                <i class="bi bi-layers text-secondary"></i>
                            </span>
                            <input type="number" name="floor_number" class="form-control border-start-0 ps-0" value="<?php echo htmlspecialchars($_POST['floor_number'] ?? '0'); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Vehicle Category</label>
                        <select name="spot_type" class="form-select">
                            <?php
                            $types   = ['Car', 'Bike', 'VIP'];
                            $current = $_POST['spot_type'] ?? 'Car';
                            foreach ($types as $t): ?>
                                <option value="<?php echo $t; ?>" <?php echo $current === $t ? 'selected' : ''; ?>>
                                    <?php echo $t; ?> Parking
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hourly Rate ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                <i class="bi bi-currency-dollar text-secondary"></i>
                            </span>
                            <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control border-start-0 ps-0" required value="<?php echo htmlspecialchars($_POST['hourly_rate'] ?? '0'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mt-4 p-3 bg-opacity-10 bg-info border border-info border-opacity-25 rounded-3">
                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" id="is_active" <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                    <label for="is_active" class="form-check-label fw-bold">
                        Set as Active / Available immediately
                    </label>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <a href="admin_parking_spot_management.php" class="text-secondary text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to Management
                    </a>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                        <i class="bi bi-save me-2"></i>Create Parking Spot
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 border-dashed bg-transparent" style="border: 2px dashed var(--glass-border);">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
                <i class="bi bi-info-circle fs-1 text-primary mb-3"></i>
                <h5>Helpful Tip</h5>
                <p class="text-secondary small">Use a consistent naming convention for spots (e.g., Level-Spot) to make it easier for users to locate their vehicles.</p>
                <hr class="w-100 border-secondary-subtle">
                <div class="small text-start w-100">
                    <div class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Auto-generates QR code</div>
                    <div class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Visible to all users</div>
                    <div><i class="bi bi-check2 text-success me-2"></i>Real-time availability</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// helper_layout_admin.php will close tags
?>
