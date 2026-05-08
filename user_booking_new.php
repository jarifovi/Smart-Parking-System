<?php
$pageTitle  = 'Node Reservation';
$sidebarKey = 'user_book_new';
require_once 'helper_layout_user.php';

$userId  = (int)($_SESSION['user_id'] ?? 0);
$error   = '';

$vehicleLabel = $_POST['vehicle_label'] ?? '';
$vehicleType  = $_POST['vehicle_type'] ?? 'Car';
$startRaw     = $_POST['start_time'] ?? '';
$endRaw       = $_POST['end_time'] ?? '';
$selectedSpot = $_POST['selected_spot_id'] ?? '';
$action       = $_POST['action'] ?? '';

function toMysqlDateTime(?string $val) {
    if (!$val) return null;
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}
function toHtmlDateTime(?string $val) {
    if (!$val) return null;
    $ts = strtotime($val);
    return $ts ? date('Y-m-d\TH:i', $ts) : null;
}

$startMysql = toMysqlDateTime($startRaw);
$endMysql   = toMysqlDateTime($endRaw);

if ($action === 'confirm_booking') {
    if (!$vehicleLabel || !$startMysql || !$endMysql || !$selectedSpot) {
        $error = 'Incomplete telemetry: Vehicle ID, time range, and node selection are required.';
    } elseif (strtotime($endMysql) <= strtotime($startMysql)) {
        $error = 'Timeline error: Exit timestamp must occur after entry.';
    }

    if (!$error) {
        $spotId = (int)$selectedSpot;
        $stmt = $databaseConnection->prepare("SELECT hourly_rate FROM parking_spots WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $spotId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $rate = (float)$row['hourly_rate'];
            $hours = max(0.5, (strtotime($endMysql) - strtotime($startMysql)) / 3600);
            $amount = $hours * $rate;

            $stmt2 = $databaseConnection->prepare("INSERT INTO bookings (user_id, spot_id, vehicle_label, vehicle_type, start_time, end_time, status, amount) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
            $stmt2->bind_param('iissssd', $userId, $spotId, $vehicleLabel, $vehicleType, $startMysql, $endMysql, $amount);
            if ($stmt2->execute()) {
                header('Location: user_bookings_list.php?success=1');
                exit;
            } else { $error = 'System fault: Failed to commit reservation to registry.'; }
        } else { $error = 'Node conflict: The selected parking node is no longer available.'; }
    }
}

$hasTimeFilter = ($action === 'check' || $action === 'confirm_booking');
$bookedSpotIds = [];
$allSpots      = [];

if ($hasTimeFilter && $startMysql && $endMysql) {
    $qSpots = $databaseConnection->query("SELECT id, spot_number, spot_type FROM parking_spots WHERE is_active = 1 ORDER BY spot_number");
    while ($row = $qSpots->fetch_assoc()) { $allSpots[] = $row; }

    $stmtB = $databaseConnection->prepare("SELECT DISTINCT spot_id FROM bookings WHERE status IN ('active','completed') AND NOT (end_time <= ? OR start_time >= ?)");
    $stmtB->bind_param('ss', $startMysql, $endMysql);
    $stmtB->execute();
    $resB = $stmtB->get_result();
    while ($row = $resB->fetch_assoc()) { $bookedSpotIds[(int)$row['spot_id']] = true; }
}
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-calendar-check-fill text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Node Reservation</h2>
            <p class="text-secondary m-0">Secure a physical node for your vehicle through the live parking grid.</p>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4"><i class="bi bi-shield-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="action" id="booking-action" value="check">
    <input type="hidden" name="selected_spot_id" id="selected_spot_id" value="<?php echo htmlspecialchars($selectedSpot); ?>">

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-primary border-opacity-10">
                <div class="card-body">
                    <h5 class="text-white opacity-75 mb-4 fw-bold">PARAMETERS</h5>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">VEHICLE IDENTITY</label>
                        <input type="text" name="vehicle_label" class="form-control" placeholder="e.g. CAS-4567" value="<?php echo htmlspecialchars($vehicleLabel); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">NODE TYPE</label>
                        <select name="vehicle_type" class="form-control">
                            <option value="Car" <?php echo $vehicleType==='Car'?'selected':''; ?>>Standard Car</option>
                            <option value="Bike" <?php echo $vehicleType==='Bike'?'selected':''; ?>>Motorcycle</option>
                            <option value="VIP" <?php echo $vehicleType==='VIP'?'selected':''; ?>>VIP Terminal</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">ENTRY EPOCH</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($startMysql)); ?>" required>
                    </div>
                    <div class="mb-5">
                        <label class="form-label text-secondary small fw-bold">EXIT EPOCH</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="<?php echo htmlspecialchars(toHtmlDateTime($endMysql)); ?>" required>
                    </div>

                    <button type="submit" class="btn-primary w-100 py-3" onclick="document.getElementById('booking-action').value='check';">
                        SCAN INFRASTRUCTURE <i class="bi bi-radar ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($hasTimeFilter && $startMysql && $endMysql): ?>
                <div class="card border-info border-opacity-10 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-white opacity-75 fw-bold m-0">LIVE GRID MAP</h5>
                            <div class="d-flex gap-3 small text-secondary fw-bold">
                                <span><i class="bi bi-square-fill text-secondary me-1"></i> BUSY</span>
                                <span><i class="bi bi-square-fill text-info me-1"></i> CHOICE</span>
                                <span><i class="bi bi-square-fill text-success me-1"></i> FREE</span>
                            </div>
                        </div>

                        <div class="parking-grid mb-5">
                            <?php foreach ($allSpots as $spot): 
                                $id = (int)$spot['id'];
                                $isBooked = isset($bookedSpotIds[$id]);
                                $isSelected = (!$isBooked && (string)$selectedSpot === (string)$id);
                                $class = 'spot-tile ' . ($isBooked ? 'booked' : ($isSelected ? 'selected' : 'available'));
                            ?>
                                <div class="<?php echo $class; ?>" data-spot-id="<?php echo $id; ?>" data-spot-label="<?php echo htmlspecialchars($spot['spot_number']); ?>">
                                    <span><?php echo htmlspecialchars($spot['spot_number']); ?></span>
                                    <span class="small-label"><?php echo $spot['spot_type']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="selection-summary" class="p-4 rounded-4 bg-info bg-opacity-5 border border-info border-opacity-10" style="<?php echo $selectedSpot ? '' : 'display:none;'; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-info fw-bold opacity-75">SELECTED NODE</div>
                                    <div class="h3 mb-0 fw-800 text-white" id="selected_spot_label"><?php echo $selectedSpot ? 'NODE #'.$selectedSpot : ''; ?></div>
                                </div>
                                <button type="submit" class="btn-primary" style="background: var(--accent-emerald) !important; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2) !important;" onclick="document.getElementById('booking-action').value='confirm_booking';">
                                    INITIATE BOOKING <i class="bi bi-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card h-100 border-info border-opacity-10 d-flex align-items-center justify-content-center py-5 text-center bg-opacity-5">
                    <div class="card-body py-5">
                        <div class="p-4 bg-info bg-opacity-5 rounded-circle d-inline-flex mb-4 border border-info border-opacity-10">
                            <i class="bi bi-map text-info fs-1"></i>
                        </div>
                        <h4 class="text-white fw-bold">Grid Sync Required</h4>
                        <p class="text-secondary small mx-auto" style="max-width: 300px;">Please define your reservation timestamps to fetch the real-time availability of the parking grid.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tiles = document.querySelectorAll('.spot-tile.available, .spot-tile.selected');
    const input = document.getElementById('selected_spot_id');
    const label = document.getElementById('selected_spot_label');
    const summ  = document.getElementById('selection-summary');

    tiles.forEach(tile => {
        tile.addEventListener('click', () => {
            const alreadySelected = tile.classList.contains('selected');
            tiles.forEach(t => { t.classList.remove('selected'); t.classList.add('available'); });

            if (alreadySelected) {
                input.value = '';
                summ.style.display = 'none';
            } else {
                tile.classList.add('selected');
                tile.classList.remove('available');
                input.value = tile.dataset.spotId;
                label.innerText = 'NODE ' + tile.dataset.spotLabel;
                summ.style.display = 'block';
            }
        });
    });
});
</script>

<?php require_once 'helper_layout_footer.php'; ?>
