<?php
$pageTitle  = 'Support & Help';
$sidebarKey = 'user_support';
require_once 'helper_layout_user.php';

$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($subject && $message) {
        // In a real app, you'd save this to a database or send an email
        // For now, we simulate success
        $success = "Your message has been sent to the support team. We will get back to you soon!";
        
        // Also simulate a system notification for the user
        $stmt = $databaseConnection->prepare("INSERT INTO user_notifications (user_id, message) VALUES (?, ?)");
        $msg = "Support Ticket Created: " . $subject;
        $stmt->bind_param("is", $loggedUser['id'], $msg);
        $stmt->execute();
    }
}
?>

<div class="page-header-main">
    <div class="page-header-title">Support & Help Center</div>
    <div class="page-header-sub">Need assistance? Send us a message and our team will help you out.</div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i><?php echo $success; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-chat-dots me-2"></i>Send a Message</div>
            <div class="card-body p-4">
                <form method="post">
                    <div class="mb-4">
                        <label class="form-label">Issue Subject</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Select a topic</option>
                            <option value="Booking Issue">Booking Issue</option>
                            <option value="Payment Problem">Payment Problem</option>
                            <option value="Account Access">Account Access</option>
                            <option value="Parking Spot Condition">Parking Spot Condition</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Message Details</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Describe your issue in detail..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                        <i class="bi bi-send me-2"></i>Submit Support Ticket
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Quick Help</div>
            <div class="card-body">
                <div class="list-group list-group-flush bg-transparent">
                    <div class="list-group-item bg-transparent border-secondary-subtle px-0 py-3">
                        <div class="fw-bold mb-1">How do I cancel a booking?</div>
                        <div class="small text-secondary">Go to "My Bookings" and look for active reservations. Currently, cancellation is only available 1 hour before start time.</div>
                    </div>
                    <div class="list-group-item bg-transparent border-secondary-subtle px-0 py-3">
                        <div class="fw-bold mb-1">Payment is not processing?</div>
                        <div class="small text-secondary">Ensure you are using the correct dummy credentials for our sandbox environment (01700000000 / 1234).</div>
                    </div>
                    <div class="list-group-item bg-transparent border-0 px-0 py-3">
                        <div class="fw-bold mb-1">Where is my QR code?</div>
                        <div class="small text-secondary">Once payment is confirmed, your receipt will contain a digital access token for entry.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-primary bg-opacity-10 border-primary border-opacity-25">
            <div class="card-body text-center p-4">
                <i class="bi bi-headset fs-1 text-primary mb-3"></i>
                <h5>24/7 Priority Support</h5>
                <p class="small text-secondary mb-0">Our developers are always online to monitor system performance and assist with critical issues.</p>
            </div>
        </div>
    </div>
</div>
<?php
// helper_layout_user.php will close tags
?>
