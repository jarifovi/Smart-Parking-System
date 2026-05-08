<?php
// admin_report_analytics.php
$pageTitle  = 'Reports & Analytics';
$sidebarKey = 'admin_reports';

require_once 'helper_layout_admin.php';   // gives $databaseConnection and opens <div class="content-area">

// ---------------------------------------------------------------------
// 1. Date range (GET ?from=YYYY-MM-DD&to=YYYY-MM-DD)
// ---------------------------------------------------------------------
$today = date('Y-m-d');
$defaultFrom = date('Y-m-01');   // first day of current month
$defaultTo   = date('Y-m-t');    // last day of current month

$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : $defaultFrom;
$to   = isset($_GET['to'])   && $_GET['to']   !== '' ? $_GET['to']   : $defaultTo;

try {
    $fromDt = (new DateTime($from))->format('Y-m-d');
    $toDt   = (new DateTime($to))->format('Y-m-d');
} catch (Exception $e) {
    $fromDt = $defaultFrom;
    $toDt   = $defaultTo;
}

$fromFull = $fromDt . ' 00:00:00';
$toFull   = $toDt   . ' 23:59:59';

// ---------------------------------------------------------------------
// 2. Top-level summary cards (revenue, transactions, bookings, avg hours)
// ---------------------------------------------------------------------
$summarySql = "
    SELECT
        COALESCE(SUM(p.amount),0)                                                    AS total_revenue,
        COUNT(DISTINCT p.id)                                                        AS txn_count,
        COUNT(DISTINCT b.id)                                                        AS booking_count,
        COALESCE(AVG(TIMESTAMPDIFF(MINUTE, b.start_time, b.end_time))/60, 0)        AS avg_hours
    FROM bookings b
    LEFT JOIN payments p
        ON p.booking_id = b.id
       AND p.payment_status = 'paid'
    WHERE b.start_time BETWEEN ? AND ?
";
$summaryStmt = $databaseConnection->prepare($summarySql);
$summaryStmt->bind_param('ss', $fromFull, $toFull);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc() ?: [
    'total_revenue' => 0,
    'txn_count'     => 0,
    'booking_count' => 0,
    'avg_hours'     => 0,
];

// ---------------------------------------------------------------------
// 3. Revenue by payment method (table)
// ---------------------------------------------------------------------
$methodSql = "
    SELECT
        p.payment_method,
        COALESCE(SUM(p.amount),0) AS total_amount
    FROM payments p
    WHERE p.payment_status = 'paid'
      AND p.created_at BETWEEN ? AND ?
    GROUP BY p.payment_method
    ORDER BY total_amount DESC
";
$methodStmt = $databaseConnection->prepare($methodSql);
$methodStmt->bind_param('ss', $fromFull, $toFull);
$methodStmt->execute();
$methodRes = $methodStmt->get_result();
$paymentMethods = [];
while ($row = $methodRes->fetch_assoc()) {
    $paymentMethods[] = $row;
}

// ---------------------------------------------------------------------
// 4. Revenue by spot type (Car / Bike / VIP) – table + chart data
// ---------------------------------------------------------------------
$spotSql = "
    SELECT
        s.spot_type,
        COUNT(DISTINCT b.id)              AS bookings,
        COALESCE(SUM(p.amount),0)        AS total_revenue
    FROM bookings b
    JOIN parking_spots s ON s.id = b.spot_id
    LEFT JOIN payments p
        ON p.booking_id = b.id
       AND p.payment_status = 'paid'
    WHERE b.start_time BETWEEN ? AND ?
    GROUP BY s.spot_type
    ORDER BY s.spot_type
";
$spotStmt = $databaseConnection->prepare($spotSql);
$spotStmt->bind_param('ss', $fromFull, $toFull);
$spotStmt->execute();
$spotRes = $spotStmt->get_result();

$spotRows     = [];
$chartLabels  = [];
$chartValues  = [];

while ($row = $spotRes->fetch_assoc()) {
    $spotRows[] = $row;
    $chartLabels[] = $row['spot_type'];
    $chartValues[] = (float)$row['total_revenue'];
}

// JSON for Chart.js
$chartLabelsJson = json_encode($chartLabels);
$chartValuesJson = json_encode($chartValues);
?>

<div class="page-header-main">
    <div class="page-header-title">Reports & Analytics</div>
    <div class="page-header-sub">Deep dive into revenue, bookings, and system performance metrics.</div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($fromDt); ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($toDt); ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-filter me-2"></i>Apply Range
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="stat-card-label">Total Revenue</div>
                <div class="stat-card-number">$<?php echo number_format($summary['total_revenue'], 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="stat-card-label">Transactions</div>
                <div class="stat-card-number"><?php echo (int)$summary['txn_count']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="stat-card-label">Total Bookings</div>
                <div class="stat-card-number"><?php echo (int)$summary['booking_count']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="stat-card-label">Avg. Duration</div>
                <div class="stat-card-number"><?php echo number_format($summary['avg_hours'], 1); ?> <small class="fs-6 text-secondary">HRS</small></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Revenue by Spot Type (table) -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-table me-2"></i>Revenue by Spot Type</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Spot Type</th>
                                <th>Bookings</th>
                                <th class="text-end">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($spotRows): ?>
                                <?php foreach ($spotRows as $row): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($row['spot_type']); ?></span></td>
                                        <td><?php echo (int)$row['bookings']; ?></td>
                                        <td class="text-end fw-bold">$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No data available for this range.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Revenue by Payment Method -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-credit-card me-2"></i>Revenue by Payment Method</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($paymentMethods): ?>
                                <?php foreach ($paymentMethods as $m): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['payment_method']); ?></td>
                                        <td class="text-end fw-bold text-success">$<?php echo number_format($m['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center text-muted py-4">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Pie chart: Revenue share by spot type -->
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Revenue Distribution</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <div style="width: 100%; max-width: 300px;">
                    <canvas id="spotRevenueChart"></canvas>
                </div>
                <?php if (!$chartLabels): ?>
                    <div class="text-muted small mt-4">No distribution data available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const labels = <?php echo $chartLabelsJson; ?>;
    const values = <?php echo $chartValuesJson; ?>;

    if (!labels.length) {
        return; // no data, no chart
    }

    const ctx = document.getElementById('spotRevenueChart').getContext('2d');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#3b82f6', // Car - Blue
                    '#10b981', // Bike - Green
                    '#f59e0b', // VIP - Amber
                    '#ef4444', // Other - Red
                    '#8b5cf6'  // Purple
                ],
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) label += ': ';
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
})();
</script>

<?php
// helper_layout_admin.php will close the .content-area, body and html tags
?>
