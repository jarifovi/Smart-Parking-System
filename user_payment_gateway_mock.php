<?php
// user_payment_gateway_mock.php
$pageTitle  = 'Mock Payment';
$sidebarKey = 'user_bookings'; // or 'user_book_new' if you prefer

require_once 'helper_layout_user.php'; // this already calls requireLogin()

$loggedUserId = $_SESSION['user_id'] ?? 0;
$errors       = [];
$booking      = null;

// ----------------------------------------------------
// 1. Get booking_id (from GET or POST) and load booking
// ----------------------------------------------------
$bookingId = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    $errors[] = 'Invalid booking reference.';
} else {
    $stmt = $databaseConnection->prepare("
        SELECT b.*
        FROM bookings b
        WHERE b.id = ? AND b.user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $bookingId, $loggedUserId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $booking = $res->fetch_assoc();
    } else {
        $errors[] = 'Booking not found or does not belong to you.';
    }
}

// If we have a booking, also check if already paid
$alreadyPaid = false;
if ($booking) {
    $stmt = $databaseConnection->prepare("
        SELECT id, transaction_id 
        FROM payments 
        WHERE booking_id = ? AND payment_status = 'paid'
        LIMIT 1
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $alreadyPaid = true;
        $existingTx  = $row['transaction_id'];
    }
}

// Payment methods (Bangladesh mobile wallets, etc.)
$allowedMethods = ['Bkash', 'Nagad', 'Rocket', ];

// Dummy credentials for mock payment
$dummyPhone = '01700000000';
$dummyPin   = '1234';

// ----------------------------------------------------
// 2. Handle POST (simulate payment)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking && !$alreadyPaid) {
    $method = $_POST['payment_method'] ?? '';
    $phone  = trim($_POST['phone'] ?? '');
    $pin    = trim($_POST['pin'] ?? '');

    if (!in_array($method, $allowedMethods, true)) {
        $errors[] = 'Please select a valid payment method.';
    }

    if ($phone !== $dummyPhone || $pin !== $dummyPin) {
        $errors[] = 'Dummy phone or PIN is incorrect. (Use 01700000000 / 1234)';
    }

    if (empty($errors)) {
        // Generate fake transaction id
        $txId = 'TX' . time() . rand(1000, 9999);

        // Insert payment
        $stmt = $databaseConnection->prepare("
            INSERT INTO payments 
                (booking_id, user_id, transaction_id, amount, payment_method, payment_status)
            VALUES 
                (?, ?, ?, ?, ?, 'paid')
        ");
        $amount = (float)$booking['amount'];
        $stmt->bind_param(
            'iisds',
            $booking['id'],
            $booking['user_id'],
            $txId,
            $amount,
            $method
        );
        $stmt->execute();

        // Mark booking as completed
        $stmt = $databaseConnection->prepare("
            UPDATE bookings 
            SET status = 'completed' 
            WHERE id = ?
        ");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();

        // (Optional) create notification row for user
        $msg = "Payment of $" . number_format($amount, 2) . " via {$method} for booking #{$bookingId} was successful.";
        $stmt = $databaseConnection->prepare("
            INSERT INTO user_notifications (user_id, message) 
            VALUES (?, ?)
        ");
        $stmt->bind_param('is', $loggedUserId, $msg);
        $stmt->execute();

        // Redirect to receipt page
        header('Location: user_payment_receipt.php?tx=' . urlencode($txId));
        exit;
    }
}
?>

<div class="page-header-main">
    <div class="page-header-title">Secure Checkout</div>
    <div class="page-header-sub">Complete your transaction using our encrypted gateway.</div>
</div>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($booking): ?>
    <div class="row g-4">
        <div class="col-lg-4 order-lg-2">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-receipt me-2"></i>Order Summary</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Booking Ref</span>
                        <span class="fw-bold">#<?php echo $booking['id']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Vehicle</span>
                        <span class="fw-bold"><?php echo htmlspecialchars($booking['vehicle_label']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-secondary small">Slot Duration</span>
                        <span class="text-end fw-bold small">
                            <?php echo date('M d, H:i', strtotime($booking['start_time'])); ?><br>
                            <?php echo date('M d, H:i', strtotime($booking['end_time'])); ?>
                        </span>
                    </div>
                    <hr class="border-secondary-subtle">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 mb-0">Total Amount</span>
                        <span class="h4 mb-0 text-success fw-bold">$<?php echo number_format($booking['amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="card border-dashed bg-transparent" style="border: 2px dashed var(--glass-border);">
                <div class="card-body small text-secondary">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-bit SSL Encrypted Payment.<br>
                    Your data is never stored on our servers.
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <?php if ($alreadyPaid): ?>
                <div class="alert alert-info py-4 text-center">
                    <i class="bi bi-check-circle fs-1 d-block mb-3"></i>
                    <h5>Payment Already Confirmed</h5>
                    <p class="mb-3">This booking has been processed successfully.</p>
                    <div class="badge bg-dark p-2 px-3 fs-6">TX ID: <?php echo htmlspecialchars($existingTx); ?></div>
                    <div class="mt-4">
                        <a href="user_bookings_list.php" class="btn btn-primary">Return to Bookings</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header"><i class="bi bi-credit-card me-2"></i>Select Payment Method</div>
                    <div class="card-body p-4">
                        <form method="post">
                            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                            
                            <div class="row g-3 mb-4">
                                <?php foreach ($allowedMethods as $m): ?>
                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="payment_method" id="method_<?php echo $m; ?>" value="<?php echo $m; ?>" required>
                                        <label class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2" for="method_<?php echo $m; ?>">
                                            <i class="bi bi-wallet2 fs-3"></i>
                                            <span class="fw-bold"><?php echo $m; ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="bg-opacity-10 bg-info border border-info border-opacity-25 rounded-3 p-3 mb-4">
                                <div class="small fw-bold text-info mb-1"><i class="bi bi-terminal me-2"></i>Developer Mock Sandbox</div>
                                <div class="small text-secondary">Use Phone: <code><?php echo $dummyPhone; ?></code> and PIN: <code><?php echo $dummyPin; ?></code></div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Wallet Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                            <i class="bi bi-phone text-secondary"></i>
                                        </span>
                                        <input type="text" name="phone" class="form-control border-start-0 ps-0" placeholder="017xxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Security PIN</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                            <i class="bi bi-shield-lock text-secondary"></i>
                                        </span>
                                        <input type="password" name="pin" class="form-control border-start-0 ps-0" placeholder="••••" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary-subtle">
                                <a href="user_bookings_list.php" class="text-secondary text-decoration-none small">
                                    <i class="bi bi-arrow-left me-1"></i> Back to History
                                </a>
                                <button type="submit" class="btn btn-success px-5 py-2 fw-bold">
                                    Confirm Payment <i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php
// helper_layout_user.php will close tags
?>
