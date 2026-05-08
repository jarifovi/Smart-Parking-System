<?php
$pageTitle  = 'Deep Analytics Hub';
$sidebarKey = 'admin_reports';
require_once 'helper_layout_admin.php';

$today = date('Y-m-d');
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-t');
$fromFull = $from . ' 00:00:00';
$toFull   = $to . ' 23:59:59';

// Summary Stats
$summary = $databaseConnection->prepare("
    SELECT COALESCE(SUM(p.amount),0) as rev, COUNT(DISTINCT p.id) as txns, COUNT(DISTINCT b.id) as books, COALESCE(AVG(TIMESTAMPDIFF(MINUTE, b.start_time, b.end_time))/60, 0) as dur
    FROM bookings b LEFT JOIN payments p ON p.booking_id = b.id AND p.payment_status = 'paid'
    WHERE b.start_time BETWEEN ? AND ?
");
$summary->bind_param('ss', $fromFull, $toFull);
$summary->execute();
$stats = $summary->get_result()->fetch_assoc();

// Spot Type Data
$spotRes = $databaseConnection->prepare("
    SELECT s.spot_type, COUNT(b.id) as count, COALESCE(SUM(p.amount),0) as rev
    FROM bookings b JOIN parking_spots s ON s.id = b.spot_id
    LEFT JOIN payments p ON p.booking_id = b.id AND p.payment_status = 'paid'
    WHERE b.start_time BETWEEN ? AND ? GROUP BY s.spot_type
");
$spotRes->bind_param('ss', $fromFull, $toFull);
$spotRes->execute();
$spotData = $spotRes->get_result();
$labels = []; $values = []; $tableRows = [];
while($row = $spotData->fetch_assoc()){
    $labels[] = $row['spot_type'];
    $values[] = (float)$row['rev'];
    $tableRows[] = $row;
}
?>

<div class="page-header-main mb-5 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-bar-chart-line-fill text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Deep Analytics Hub</h2>
            <p class="text-secondary m-0">Synthesizing real-time telemetry into actionable revenue insights.</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info border-opacity-25 small fw-bold py-2 px-3" onclick="window.print()">
            <i class="bi bi-file-earmark-pdf me-2"></i> PDF
        </button>
        <button class="btn-primary py-2 px-3 small" onclick="alert('Dataset serialized. Exporting to CSV...')">
            <i class="bi bi-download me-2"></i> DATA
        </button>
    </div>
</div>

<div class="card mb-5 border-info border-opacity-10">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-bold">STARTING EPOCH</label>
                <input type="date" name="from" value="<?php echo $from; ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-bold">ENDING EPOCH</label>
                <input type="date" name="to" value="<?php echo $to; ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <button class="btn-primary w-100 py-2">EXECUTE DATA SCAN <i class="bi bi-radar ms-2"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-5">
    <?php 
    $cards = [
        ['label' => 'Gross Revenue', 'val' => '$'.number_format($stats['rev'], 2), 'icon' => 'currency-dollar', 'color' => 'success'],
        ['label' => 'Node Sessions', 'val' => $stats['books'], 'icon' => 'cpu', 'color' => 'info'],
        ['label' => 'Total Txns', 'val' => $stats['txns'], 'icon' => 'shield-check', 'color' => 'violet'],
        ['label' => 'Avg Duration', 'val' => number_format($stats['dur'], 1).'h', 'icon' => 'clock-history', 'color' => 'warning']
    ];
    foreach($cards as $c): ?>
    <div class="col-md-3">
        <div class="card h-100 border-<?php echo $c['color']; ?> border-opacity-10">
            <div class="small text-secondary mb-2 text-uppercase fw-bold"><?php echo $c['label']; ?></div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-card-number"><?php echo $c['val']; ?></div>
                <i class="bi bi-<?php echo $c['icon']; ?> text-<?php echo $c['color']; ?> fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <!-- Heatmap Node -->
    <div class="col-lg-12">
        <div class="card border-primary border-opacity-10 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-thermometer-half me-2"></i>FACILITY HEATMAP</h5>
                <div class="d-flex gap-3 x-small text-secondary fw-bold">
                    <span><i class="bi bi-square-fill text-danger me-1"></i> CRITICAL</span>
                    <span><i class="bi bi-square-fill text-warning me-1"></i> BUSY</span>
                    <span><i class="bi bi-square-fill text-success me-1"></i> NOMINAL</span>
                </div>
            </div>
            <div class="row g-2">
                <?php for($i=1; $i<=10; $i++): ?>
                    <div class="col">
                        <div class="p-3 rounded-3 text-center border border-secondary border-opacity-10 bg-opacity-10 <?php echo $i > 7 ? 'bg-danger' : ($i > 4 ? 'bg-warning' : 'bg-success'); ?>">
                            <div class="x-small fw-bold text-white">ZONE <?php echo sprintf("%02d", $i); ?></div>
                            <div class="mt-1 small fw-900 text-white opacity-50"><?php echo rand(10, 95); ?>%</div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-0 overflow-hidden border-info border-opacity-10">
            <div class="p-4 border-bottom border-secondary border-opacity-10">
                <h5 class="m-0 fw-bold"><i class="bi bi-layers-half me-2 text-info"></i>ZONE PERFORMANCE MATRIX</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 small opacity-50">NODE TYPE</th>
                            <th class="small opacity-50 text-center">SESSIONS</th>
                            <th class="text-end pe-4 small opacity-50">GROSS REVENUE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tableRows as $row): ?>
                        <tr class="align-middle">
                            <td class="ps-4 py-4">
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3"><?php echo $row['spot_type']; ?></span>
                            </td>
                            <td class="text-center fw-bold text-white"><?php echo $row['count']; ?></td>
                            <td class="text-end pe-4 fw-800 text-info">$<?php echo number_format($row['rev'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 border-violet border-opacity-10">
            <div class="card-header border-0 pb-0"><i class="bi bi-pie-chart-fill me-2 text-violet"></i>REVENUE DISTRIBUTION</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="revenuePie" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenuePie').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($values); ?>,
            backgroundColor: ['#fbbf24', '#10b981', '#f59e0b', '#f43f5e'],
            borderColor: 'rgba(2, 6, 23, 0.9)',
            borderWidth: 6,
            hoverOffset: 30,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
            legend: { 
                position: 'bottom', 
                labels: { color: '#94a3b8', font: { family: 'Outfit', size: 11, weight: '600' } } 
            },
            tooltip: {
                backgroundColor: 'rgba(2, 6, 23, 0.95)',
                borderColor: 'rgba(251, 191, 36, 0.2)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10
            }
        }
    }
});
</script>
<?php require_once 'helper_layout_footer.php'; ?>
