<?php
// user_payment_receipt.php
$pageTitle  = 'Payment Receipt';
$sidebarKey = 'user_bookings';

require_once 'helper_layout_user.php'; // includes requireLogin()

$loggedId = $_SESSION['user_id'] ?? 0;
$tx       = $_GET['tx'] ?? '';

if (!$tx) {
    echo "<h3 class='text-white'>Invalid receipt request.</h3>";
    exit;
}

// Look for the payment
$stmt = $databaseConnection->prepare("
    SELECT p.*, b.spot_id, b.vehicle_label, b.vehicle_type, b.start_time, b.end_time, ps.spot_number 
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN parking_spots ps ON b.spot_id = ps.id
    WHERE p.transaction_id = ? AND p.user_id = ?
    LIMIT 1
");
$stmt->bind_param('si', $tx, $loggedId);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || !$res->num_rows) {
    echo "<h3 class='text-white'>Invalid or unauthorized receipt request.</h3>";
    exit;
}

$payment = $res->fetch_assoc();
?>

<div class="page-header-main d-flex justify-content-between align-items-center">
    <div>
        <div class="page-header-title">Payment Receipt</div>
        <div class="page-header-sub">Your transaction was successful. Keep this for your records.</div>
    </div>
    <button class="btn btn-outline-light" onclick="window.print()">
        <i class="bi bi-printer me-2"></i>Print Receipt
    </button>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card overflow-hidden">
            <div class="card-header bg-success bg-opacity-10 py-4 text-center border-0">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-lg fs-2"></i>
                </div>
                <h4 class="mb-0 text-success">Transaction Successful</h4>
                <div class="small text-secondary mt-1">Transaction ID: <code><?php echo htmlspecialchars($payment['transaction_id']); ?></code></div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 mb-5">
                    <div class="col-sm-6">
                        <div class="text-secondary small mb-1 uppercase tracking-wider">PAYMENT TO</div>
                        <div class="fw-bold fs-5">Smart Parking System</div>
                        <div class="small text-secondary">Developer Central Plaza</div>
                        <div class="small text-secondary">Dhaka, Bangladesh</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <div class="text-secondary small mb-1 uppercase tracking-wider">DATE & TIME</div>
                        <div class="fw-bold fs-5"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></div>
                        <div class="small text-secondary"><?php echo date('H:i:s', strtotime($payment['created_at'])); ?></div>
                    </div>
                </div>

                <div class="table-responsive mb-5">
                    <table class="table table-borderless align-middle">
                        <thead class="border-bottom border-secondary-subtle">
                            <tr>
                                <th class="ps-0 py-3 small text-secondary">DESCRIPTION</th>
                                <th class="text-center py-3 small text-secondary">SPOT</th>
                                <th class="text-end pe-0 py-3 small text-secondary">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-0 py-4">
                                    <div class="fw-bold fs-6">Parking Reservation</div>
                                    <div class="small text-secondary">Vehicle: <?php echo htmlspecialchars($payment['vehicle_label']); ?> (<?php echo htmlspecialchars($payment['vehicle_type']); ?>)</div>
                                    <div class="small text-secondary">Period: <?php echo date('H:i', strtotime($payment['start_time'])); ?> - <?php echo date('H:i', strtotime($payment['end_time'])); ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info px-3 py-2"><?php echo htmlspecialchars($payment['spot_number']); ?></span>
                                </td>
                                <td class="text-end pe-0 fw-bold fs-5">$<?php echo number_format($payment['amount'], 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="border-top border-secondary-subtle">
                            <tr>
                                <td colspan="2" class="text-end py-4">
                                    <div class="fw-bold fs-5">Total Paid</div>
                                    <div class="small text-secondary">via <?php echo htmlspecialchars($payment['payment_method']); ?> Wallet</div>
                                </td>
                                <td class="text-end pe-0 py-4">
                                    <div class="fw-bold fs-3 text-success">$<?php echo number_format($payment['amount'], 2); ?></div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-md-8">
                        <div class="bg-opacity-10 bg-info border border-info border-opacity-25 rounded-3 p-4">
                            <div class="small fw-bold text-info mb-2"><i class="bi bi-info-circle me-2"></i>Parking Instructions</div>
                            <div class="small text-secondary">Please ensure your vehicle is parked within the lines of spot <strong><?php echo htmlspecialchars($payment['spot_number']); ?></strong>. Failure to vacate by <strong><?php echo date('H:i', strtotime($payment['end_time'])); ?></strong> may result in additional charges.</div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="d-inline-block p-3 bg-white rounded-3 mb-2 shadow-sm">
                            <!-- Using a public QR API for demonstration -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=BOOKING-<?php echo $payment['booking_id']; ?>" alt="Access QR" style="width: 120px;">
                        </div>
                        <div class="small text-secondary fw-bold">DIGITAL ACCESS TOKEN</div>
                        <div class="small text-secondary opacity-50" style="font-size: 0.6rem;">Scan at Entry/Exit Gate</div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 p-4 text-center">
                <a href="user_bookings_list.php" class="btn btn-primary px-5 py-2 fw-bold mb-3">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Bookings
                </a>
                <div class="small text-secondary">Thank you for using our service!</div>
            </div>
        </div>
    </div>
</div>
<?php
// helper_layout_user.php will close tags
?>
