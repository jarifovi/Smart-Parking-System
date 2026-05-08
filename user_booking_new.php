<?php
// user_booking_new.php
$pageTitle  = 'Book Parking Spot';
$sidebarKey = 'user_book_new';

require_once 'helper_layout_user.php';

$userId  = (int)($_SESSION['user_id'] ?? 0);
$message = '';
$error   = '';

$vehicleLabel = $_POST['vehicle_label'] ?? '';
$vehicleType  = $_POST['vehicle_type'] ?? 'Car';
$startRaw     = $_POST['start_time'] ?? '';
$endRaw       = $_POST['end_time'] ?? '';
$selectedSpot = $_POST['selected_spot_id'] ?? '';

$action = $_POST['action'] ?? '';

/**
 * Convert datetime-local (Y-m-dTH:i) from HTML to MySQL DATETIME.
 */
function toMysqlDateTime(?string $val): ?string {
    if (!$val) return null;
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}

/**
 * Convert MySQL DATETIME back to datetime-local format for the input value.
 */
function toHtmlDateTime(?string $val): ?string {
    if (!$val) return null;
    $ts = strtotime($val);
    return $ts ? date('Y-m-d\TH:i', $ts) : null;
}

$startMysql = toMysqlDateTime($startRaw);
$endMysql   = toMysqlDateTime($endRaw);

// ============ STEP 2: CONFIRM BOOKING & REDIRECT TO PAYMENT ============
if ($action === 'confirm_booking') {

    if (!$userId) {
        $error = 'You must be logged in to book.';
    } elseif (!$vehicleLabel || !$startMysql || !$endMysql || !$selectedSpot) {
        $error = 'Vehicle, time range and parking spot are required.';
    } elseif (strtotime($endMysql) <= strtotime($startMysql)) {
        $error = 'End time must be after start time.';
    }

    if (!$error) {
        $spotId = (int)$selectedSpot;

        // Try to get hourly rate, prefer is_active if column exists
        $stmt = $databaseConnection->prepare("
            SELECT hourly_rate 
            FROM parking_spots 
            WHERE id = ? AND is_active = 1
        ");
        if ($stmt === false) {
            // is_active column might not exist – fallback without it
            $stmt = $databaseConnection->prepare("
                SELECT hourly_rate 
                FROM parking_spots 
                WHERE id = ?
            ");
        }

        $stmt->bind_param('i', $spotId);
        $stmt->execute();
        $res = $stmt->get_result();

        if (!$row = $res->fetch_assoc()) {
            $error = 'Selected spot is not available.';
        } else {
            $rate = (float)$row['hourly_rate'];

            // Calculate amount
            $hours = (strtotime($endMysql) - strtotime($startMysql)) / 3600;
            if ($hours < 0.5) $hours = 0.5; // minimum 30 minutes
            $amount = $hours * $rate;

            $stmt2 = $databaseConnection->prepare("
                INSERT INTO bookings 
                    (user_id, spot_id, vehicle_label, vehicle_type, start_time, end_time, status, amount)
                VALUES (?, ?, ?, ?, ?, ?, 'active', ?)
            ");
            $stmt2->bind_param(
                'iissssd',
                $userId,
                $spotId,
                $vehicleLabel,
                $vehicleType,
                $startMysql,
                $endMysql,
                $amount
            );

            if ($stmt2->execute()) {
                $bookingId = $stmt2->insert_id;
                header('Location: user_payment_gateway_mock.php?booking_id=' . $bookingId);
                exit;
            } else {
                $error = 'Failed to create booking. Please try again.';
            }
        }
    }
}

// ============ STEP 1: CHECK AVAILABLE SPOTS ============

$hasTimeFilter = ($action === 'check' || $action === 'confirm_booking');
$bookedSpotIds = [];
$allSpots      = [];

if ($hasTimeFilter && $startMysql && $endMysql) {

    // 1) Fetch all spots (prefer active=1, but fallback if column missing)
    $qSpots = $databaseConnection->query("
        SELECT id, spot_number, spot_type 
        FROM parking_spots 
        WHERE is_active = 1 
        ORDER BY spot_number
    ");
    if ($qSpots === false) {
        // If is_active column doesn't exist, fallback to all spots
        $qSpots = $databaseConnection->query("
            SELECT id, spot_number, spot_type 
            FROM parking_spots 
            ORDER BY spot_number
        ");
    }

    if ($qSpots) {
        while ($row = $qSpots->fetch_assoc()) {
            $allSpots[] = $row;
        }
    }

    // 2) Find spots that are booked in the selected time range
    $stmtB = $databaseConnection->prepare("
        SELECT DISTINCT spot_id 
        FROM bookings 
        WHERE status IN ('active','completed')
          AND NOT (end_time <= ? OR start_time >= ?)
    ");
    $stmtB->bind_param('ss', $startMysql, $endMysql);
    $stmtB->execute();
    $resB = $stmtB->get_result();
    while ($row = $resB->fetch_assoc()) {
        $bookedSpotIds[(int)$row['spot_id']] = true;
    }
}
?>

<div class="page-header-main">
    <div>
        <div class="page-header-title">Book a Parking Spot</div>
        <div class="page-header-sub">Choose time and a free slot from the map below.</div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger py-2 small">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="action" id="booking-action" value="check">
    <input type="hidden" name="selected_spot_id" id="selected_spot_id" value="<?php echo htmlspecialchars($selectedSpot); ?>">

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-primary border-opacity-10 h-100">
                <div class="card-header border-0 pb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i>BOOKING PARAMETERS</div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">VEHICLE IDENTITY</label>
                        <input type="text" name="vehicle_label" class="form-control" placeholder="e.g. CAS-4567" value="<?php echo htmlspecialchars($vehicleLabel); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">NODE TYPE</label>
                        <select name="vehicle_type" class="form-control">
                            <option value="Car"  <?php echo $vehicleType === 'Car' ? 'selected' : ''; ?>>Standard Car</option>
                            <option value="Bike" <?php echo $vehicleType === 'Bike' ? 'selected' : ''; ?>>Motorcycle</option>
                            <option value="VIP"  <?php echo $vehicleType === 'VIP' ? 'selected' : ''; ?>>VIP / Reserved</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">ENTRY TIME</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($startMysql)); ?>" required>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small fw-bold">EXIT TIME</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($endMysql)); ?>" required>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn-primary w-100" onclick="document.getElementById('booking-action').value='check';">
                            SCAN FOR AVAILABILITY <i class="bi bi-broadcast ms-2"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary border-opacity-25 w-100" onclick="window.location.href='user_booking_new.php';">
                            RESET NODES
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($hasTimeFilter && $startMysql && $endMysql): ?>
                <div class="card border-info border-opacity-10">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                        <span class="fw-bold"><i class="bi bi-cpu me-2 text-info"></i>LIVE PARKING GRID</span>
                        <div class="d-flex gap-3 small text-secondary">
                            <span><span class="parking-legend-box parking-legend-available"></span>Free</span>
                            <span><span class="parking-legend-box parking-legend-selected"></span>Choice</span>
                            <span><span class="parking-legend-box parking-legend-booked"></span>Busy</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="parking-grid mb-4">
                            <?php if (!$allSpots): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                                    <p class="text-secondary mt-2">No parking nodes detected in this sector.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($allSpots as $spot):
                                    $id    = (int)$spot['id'];
                                    $label = $spot['spot_number'];
                                    $type  = $spot['spot_type'];
                                    $isBooked   = isset($bookedSpotIds[$id]);
                                    $isSelected = (!$isBooked && (string)$selectedSpot === (string)$id);
                                    $classes = 'spot-tile';
                                    if ($isBooked) { $classes .= ' booked'; } 
                                    else { $classes .= ' available'; if ($isSelected) { $classes .= ' selected'; } }
                                ?>
                                    <div class="<?php echo $classes; ?>" data-spot-id="<?php echo $id; ?>" data-spot-label="<?php echo htmlspecialchars($label); ?>">
                                        <span><?php echo htmlspecialchars($label); ?></span>
                                        <span class="small-label"><?php echo htmlspecialchars($type); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div id="selection-summary" class="p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-25 mb-4" style="<?php echo $selectedSpot ? 'animation: slideInUp 0.5s ease-out;' : 'display:none;'; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-info fw-bold opacity-75">SELECTED SLOT IDENTITY</div>
                                    <div class="h3 mb-0 fw-800 text-white" id="selected_spot_label"><?php echo $selectedSpot ? htmlspecialchars($selectedSpot) : 'None'; ?></div>
                                </div>
                                <button type="submit" class="btn-primary bg-success border-0 px-4" onclick="document.getElementById('booking-action').value='confirm_booking';">
                                    INITIALIZE BOOKING <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <?php if (!$selectedSpot): ?>
                            <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary border-opacity-10 text-center text-secondary small">
                                <i class="bi bi-cursor-fill me-2 text-info"></i>Select an available node from the grid above to proceed.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card h-100 border-dashed border-opacity-25 d-flex align-items-center justify-content-center py-5 text-center">
                    <div class="card-body py-5">
                        <div class="p-4 bg-dark bg-opacity-50 rounded-circle d-inline-flex mb-4 border border-secondary border-opacity-10" style="animation: float 6s infinite ease-in-out;">
                            <i class="bi bi-calendar-event text-secondary fs-1"></i>
                        </div>
                        <h4 class="text-white fw-bold">Node Scan Required</h4>
                        <p class="text-secondary small mx-auto" style="max-width: 300px;">Please define your entry and exit timestamps on the left to scan for available parking nodes.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tiles       = document.querySelectorAll('.spot-tile.available');
    const hiddenInput = document.getElementById('selected_spot_id');
    const labelSpan   = document.getElementById('selected_spot_label');
    const paySection  = document.getElementById('selection-summary');

    tiles.forEach(function (tile) {
        tile.addEventListener('click', function (e) {
            e.preventDefault();

            const alreadySelected = this.classList.contains('selected');

            // Clear all selections
            tiles.forEach(t => t.classList.remove('selected'));

            if (alreadySelected) {
                // Unselect
                hiddenInput.value = '';
                if (labelSpan) labelSpan.textContent = 'None';
                if (paySection) paySection.style.display = 'none';
            } else {
                // Select this tile
                this.classList.add('selected');
                const id    = this.dataset.spotId;
                const label = this.dataset.spotLabel;
                hiddenInput.value = id;
                if (labelSpan) labelSpan.textContent = label;
                if (paySection) paySection.style.display = 'block';
                
                // Smooth scroll to summary on mobile
                if (window.innerWidth < 992) {
                    paySection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
});
</script>
<?php
// helper_layout_user.php will close the main content area
?>
