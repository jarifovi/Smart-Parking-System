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

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-info-circle me-2"></i>Booking Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Vehicle Label</label>
                        <input type="text" name="vehicle_label" class="form-control" placeholder="e.g. CAS-4567" value="<?php echo htmlspecialchars($vehicleLabel); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select">
                            <option value="Car"  <?php echo $vehicleType === 'Car' ? 'selected' : ''; ?>>Car</option>
                            <option value="Bike" <?php echo $vehicleType === 'Bike' ? 'selected' : ''; ?>>Bike</option>
                            <option value="VIP"  <?php echo $vehicleType === 'VIP' ? 'selected' : ''; ?>>VIP</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($startMysql)); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($endMysql)); ?>" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" onclick="document.getElementById('booking-action').value='check';">
                            <i class="bi bi-search me-2"></i>Check Availability
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='user_booking_new.php';">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($hasTimeFilter && $startMysql && $endMysql): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-grid-3x3-gap me-2"></i>Parking Slot Selection</span>
                        <div class="parking-grid-legend mb-0">
                            <span><span class="parking-legend-box parking-legend-available"></span>Free</span>
                            <span><span class="parking-legend-box parking-legend-selected"></span>Choice</span>
                            <span><span class="parking-legend-box parking-legend-booked"></span>Taken</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="parking-grid mb-4">
                            <?php if (!$allSpots): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-circle text-muted fs-1"></i>
                                    <p class="text-muted mt-2">No parking spots configured for this facility.</p>
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

                        <div id="selection-summary" class="p-3 rounded-3 bg-opacity-10 bg-info border border-info mb-4" style="<?php echo $selectedSpot ? '' : 'display:none;'; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-uppercase opacity-75">Target Slot</div>
                                    <div class="h5 mb-0 fw-bold" id="selected_spot_label"><?php echo $selectedSpot ? htmlspecialchars($selectedSpot) : 'None'; ?></div>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="document.getElementById('booking-action').value='confirm_booking';">
                                    Book This Spot <i class="bi bi-arrow-right-circle ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <?php if (!$selectedSpot): ?>
                            <div class="alert alert-info border-0 bg-opacity-10 bg-info text-info small mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i>Please click on an available (blue) spot above to select it.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-dashed d-flex align-items-center justify-content-center py-5 text-center bg-transparent" style="border: 2px dashed var(--glass-border);">
                    <div class="card-body">
                        <i class="bi bi-calendar-range text-secondary fs-1 mb-3"></i>
                        <h5>Set Your Schedule</h5>
                        <p class="text-secondary small">Define your arrival and departure times to see available spots.</p>
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
