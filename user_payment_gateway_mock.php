<?php
// 1. ALL LOGIC AND REDIRECTS MUST HAPPEN BEFORE ANY HTML OUTPUT
require_once 'config_database.php';
require_once 'helper_authentication.php';
requireLogin();

$loggedUserId = (int)$_SESSION['user_id'];
$errors       = [];
$booking      = null;
$bookingId    = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);

// Load Booking Telemetry
if ($bookingId > 0) {
    $stmt = $databaseConnection->prepare("SELECT b.* FROM bookings b WHERE b.id = ? AND b.user_id = ? LIMIT 1");
    $stmt->bind_param('ii', $bookingId, $loggedUserId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $booking = $res->fetch_assoc();
    } else { $errors[] = 'Node sequence invalid: Booking not found.'; }
}

// Check status
$alreadyPaid = false;
if ($booking) {
    $stmt = $databaseConnection->prepare("SELECT transaction_id FROM payments WHERE booking_id = ? AND payment_status = 'paid' LIMIT 1");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) { $alreadyPaid = true; }
}

// Handle Mock Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking && !$alreadyPaid) {
    $method = $_POST['payment_method'] ?? '';
    $phone  = trim($_POST['phone'] ?? '');
    $pin    = trim($_POST['pin'] ?? '');

    if ($phone !== '01700000000' || $pin !== '1234') {
        $errors[] = 'Neural validation failed: Use 01700000000 / 1234.';
    }

    if (empty($errors)) {
        $txId = 'TX' . time() . rand(1000, 9999);
        $amount = (float)$booking['amount'];

        // Transaction Entry
        $stmt = $databaseConnection->prepare("INSERT INTO payments (booking_id, user_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, 'paid')");
        $stmt->bind_param('iisds', $bookingId, $loggedUserId, $txId, $amount, $method);
        $stmt->execute();

        // Node Finalization
        $stmt = $databaseConnection->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();

        // System Alert
        $msg = "Transaction verified: $" . number_format($amount, 2) . " via {$method} for Node #{$bookingId}.";
        $stmt = $databaseConnection->prepare("INSERT INTO user_notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param('is', $loggedUserId, $msg);
        $stmt->execute();

        // REDIRECT WORKS HERE BECAUSE NO HTML SENT
        header('Location: user_payment_receipt.php?tx=' . urlencode($txId));
        exit;
    }
}

// 2. INITIALIZE LUXURY LAYOUT
$pageTitle  = 'Secure Checkout';
$sidebarKey = 'user_bookings';
require_once 'helper_layout_user.php';
?>

<div class="page-header-main mb-5">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
            <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
        </div>
        <div>
            <h2 class="fw-800 text-white m-0">Secure Checkout</h2>
            <p class="text-secondary m-0">256-bit encrypted node transaction terminal.</p>
        </div>
    </div>
</div>

<?php if (!empty($errors)): foreach ($errors as $e): ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4 rounded-4"><i class="bi bi-shield-exclamation me-2"></i><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; endif; ?>

<?php if ($booking): ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <?php if ($alreadyPaid): ?>
                <div class="card border-success border-opacity-10 py-5 text-center bg-opacity-5">
                    <div class="p-4 bg-success bg-opacity-5 rounded-circle d-inline-flex mb-4 border border-success border-opacity-10">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    </div>
                    <h4 class="text-white fw-bold">Transaction Confirmed</h4>
                    <p class="text-secondary small">This node has already been provisioned and paid.</p>
                    <div class="mt-4">
                        <a href="user_bookings_list.php" class="btn-primary px-5">BACK TO LEDGER</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="post" class="card border-primary border-opacity-10">
                    <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                    <h5 class="text-white opacity-75 fw-bold mb-4">SELECT PAYMENT INTERFACE</h5>
                    
                    <div class="row g-3 mb-5">
                        <?php foreach (['Bkash', 'Nagad', 'Rocket'] as $m): ?>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="payment_method" id="m_<?php echo $m; ?>" value="<?php echo $m; ?>" required>
                                <label class="btn btn-outline-primary w-100 py-4 rounded-4 d-flex flex-column align-items-center gap-2 border-opacity-25" for="m_<?php echo $m; ?>">
                                    <i class="bi bi-wallet2 fs-2"></i>
                                    <span class="fw-bold small"><?php echo strtoupper($m); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-4 bg-info bg-opacity-5 rounded-4 border border-info border-opacity-10 mb-5">
                        <div class="small fw-bold text-info mb-2"><i class="bi bi-cpu me-2"></i>MOCK SANDBOX CREDENTIALS</div>
                        <div class="x-small text-secondary">Phone: <code class="text-info">01700000000</code> | PIN: <code class="text-info">1234</code></div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">WALLET NUMBER</label>
                            <input type="text" name="phone" class="form-control" placeholder="017xxxxxxxx" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">SECURE PIN</label>
                            <input type="password" name="pin" class="form-control" placeholder="••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-4 border-top border-secondary border-opacity-10">
                        <a href="user_bookings_list.php" class="text-secondary text-decoration-none small fw-bold"><i class="bi bi-arrow-left me-2"></i>CANCEL</a>
                        <button type="submit" class="btn-primary px-5 py-3">CONFIRM TRANSACTION <i class="bi bi-chevron-right ms-2"></i></button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-info border-opacity-10 mb-4">
                <h5 class="text-white opacity-75 fw-bold mb-4">ORDER SUMMARY</h5>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">NODE REF</span>
                    <span class="fw-bold text-white">#<?php echo $booking['id']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary small">VEHICLE</span>
                    <span class="fw-bold text-white"><?php echo htmlspecialchars($booking['vehicle_label']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary small">DURATION</span>
                    <div class="text-end fw-bold text-white x-small">
                        <?php echo date('M d, H:i', strtotime($booking['start_time'])); ?> - <br>
                        <?php echo date('M d, H:i', strtotime($booking['end_time'])); ?>
                    </div>
                </div>
                <hr class="border-secondary border-opacity-10 my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white fw-bold">TOTAL</span>
                    <span class="h3 mb-0 text-success fw-900">$<?php echo number_format($booking['amount'], 2); ?></span>
                </div>
            </div>
            
            <div class="p-3 rounded-4 bg-success bg-opacity-5 border border-success border-opacity-10 text-center">
                <i class="bi bi-shield-check text-success fs-4 mb-2 d-block"></i>
                <div class="x-small text-secondary fw-bold">256-bit SSL ENCRYPTED GATEWAY</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'helper_layout_footer.php'; ?>
