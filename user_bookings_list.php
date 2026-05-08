<?php
$pageTitle  = 'Session Ledger';
$sidebarKey = 'user_bookings';
require_once 'helper_layout_user.php';

$userId = $_SESSION['user_id'] ?? 0;
$activeTab = $_GET['tab'] ?? 'all';

$where = 'b.user_id = ?';
$params = [$userId];
$types  = 'i';

if ($activeTab !== 'all') {
    $where .= ' AND b.status = ?';
    $params[] = $activeTab;
    $types   .= 's';
}

$sql = "
    SELECT b.*, ps.spot_number,
    CASE WHEN EXISTS (SELECT 1 FROM payments p WHERE p.booking_id = b.id AND p.payment_status = 'paid') THEN 1 ELSE 0 END AS has_paid
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

<div class="page-header-main mb-5 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-clock-history text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Session Ledger</h2>
            <p class="text-secondary m-0">Historical telemetry and active node access keys.</p>
        </div>
    </div>
    <a href="user_booking_new.php" class="btn-primary">
        <i class="bi bi-plus-lg me-2"></i> NEW SESSION
    </a>
</div>

<div class="card mb-5 border-info border-opacity-10">
    <div class="card-body p-2">
        <div class="nav nav-pills nav-fill gap-2">
            <?php foreach(['all' => 'ALL NODES', 'active' => 'ACTIVE', 'completed' => 'HISTORY', 'cancelled' => 'VOID'] as $key => $label): ?>
                <a href="?tab=<?php echo $key; ?>" class="nav-link border border-secondary border-opacity-10 <?php echo $activeTab === $key ? 'active' : ''; ?>">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card p-0 overflow-hidden border-info border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50 py-3">NODE ID</th>
                    <th class="small opacity-50 py-3">SPOT</th>
                    <th class="small opacity-50 py-3">VEHICLE</th>
                    <th class="small opacity-50 py-3">TIMELINE</th>
                    <th class="small opacity-50 py-3 text-center">STATUS</th>
                    <th class="text-end pe-4 small opacity-50 py-3">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): while ($row = $result->fetch_assoc()): 
                    $status = $row['status'];
                    $hasPaid = (int)$row['has_paid'] === 1;
                ?>
                    <tr class="align-middle">
                        <td class="ps-4 py-4"><code>#<?php echo $row['id']; ?></code></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3"><?php echo htmlspecialchars($row['spot_number']); ?></span></td>
                        <td class="fw-bold text-white"><?php echo htmlspecialchars($row['vehicle_label']); ?></td>
                        <td class="small text-secondary">
                            <div class="fw-bold text-white"><?php echo date('M d, H:i', strtotime($row['start_time'])); ?></div>
                            <div class="opacity-50"><?php echo date('M d, H:i', strtotime($row['end_time'])); ?></div>
                        </td>
                        <td class="text-center">
                            <?php $clr = $status==='active' ? 'success' : ($status==='completed' ? 'primary' : 'danger'); ?>
                            <span class="badge bg-<?php echo $clr; ?> bg-opacity-10 text-<?php echo $clr; ?> border border-<?php echo $clr; ?> border-opacity-25 px-3"><?php echo strtoupper($status); ?></span>
                            <?php if ($hasPaid): ?><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">PAID</span><?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <?php if ($status === 'active'): ?>
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-sm btn-outline-info border-opacity-25 px-3 x-small fw-bold" onclick="showQR(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-qr-code me-1"></i> ACCESS KEY
                                    </button>
                                    <?php if (!$hasPaid): ?>
                                        <a href="user_payment_gateway_mock.php?booking_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary py-1 x-small fw-bold">PAY NOW</a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-secondary opacity-25 x-small fw-bold">NO ACTIONS</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-secondary opacity-50">No telemetry logs found for this filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- NEURAL QR MODAL -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card p-0 border-primary border-opacity-25">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <div class="h5 fw-bold text-white mb-1">NODE ACCESS KEY</div>
                    <div class="small text-secondary">Authorized personnel only. Scan at gate entry.</div>
                </div>
                <div class="p-4 bg-white rounded-4 d-inline-block shadow-lg mb-4">
                    <img id="qrImage" src="" alt="Access QR" style="width: 180px; height: 180px;">
                </div>
                <div class="x-small text-info fw-bold mb-4">NODE ID: <span id="qrNodeId"></span></div>
                <button type="button" class="btn btn-outline-secondary border-opacity-25 w-100 fw-bold x-small" data-bs-dismiss="modal">CLOSE TERMINAL</button>
            </div>
        </div>
    </div>
</div>

<script>
function showQR(id) {
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    document.getElementById('qrImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SP_NODE_${id}&bgcolor=ffffff&color=020617`;
    document.getElementById('qrNodeId').innerText = '#' + id;
    modal.show();
}
</script>

<?php require_once 'helper_layout_footer.php'; ?>
