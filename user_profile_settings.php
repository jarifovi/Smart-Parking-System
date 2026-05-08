<?php
require_once 'helper_authentication.php';
requireLogin();
$user = getLoggedUser($databaseConnection);
$userId = $user['id'];

$errors = [];
$success = "";

/* -------------------------------------------------
   1. Load Notification Settings (new user_settings table)
--------------------------------------------------- */

$stmt = $databaseConnection->prepare("
    SELECT * FROM user_settings WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

/* If no settings exist — create defaults */
if (!$settings) {
    $settings = [
        'confirm_booking' => 1,
        'reminder_booking' => 1,
        'expiry_alert' => 1
    ];
}

/* -------------------------------------------------
   2. Save changes when form is submitted
--------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Update Profile Info */
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = trim($_POST['new_password']);

    if (!password_verify($currentPassword, $user['password_hash'])) {
        $errors[] = "Current password is incorrect.";
    } else {
        if ($newPassword !== "") {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $databaseConnection->prepare("
                UPDATE users SET full_name=?, email=?, password_hash=? WHERE id=?
            ");
            $stmt->bind_param("sssi", $fullName, $email, $newHash, $userId);
        } else {
            $stmt = $databaseConnection->prepare("
                UPDATE users SET full_name=?, email=? WHERE id=?
            ");
            $stmt->bind_param("ssi", $fullName, $email, $userId);
        }
        $stmt->execute();
    }

    /* Save Notification Settings */
    $confirm = isset($_POST['confirm_booking']) ? 1 : 0;
    $reminder = isset($_POST['reminder_booking']) ? 1 : 0;
    $expiry = isset($_POST['expiry_alert']) ? 1 : 0;

    $stmt = $databaseConnection->prepare("
        INSERT INTO user_settings (user_id, confirm_booking, reminder_booking, expiry_alert)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            confirm_booking = VALUES(confirm_booking),
            reminder_booking = VALUES(reminder_booking),
            expiry_alert = VALUES(expiry_alert)
    ");
    $stmt->bind_param("iiii", $userId, $confirm, $reminder, $expiry);
    $stmt->execute();

    $success = "Profile updated successfully!";
}

require 'helper_layout_user.php';
?>

<div class="page-header-main">
    <div class="page-header-title">Profile Settings</div>
    <div class="page-header-sub">Manage your account credentials and notification preferences.</div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="post">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-person-gear me-2"></i>Account Information</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-person text-secondary"></i>
                                </span>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control border-start-0 ps-0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-envelope text-secondary"></i>
                                </span>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control border-start-0 ps-0" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-key text-secondary"></i>
                                </span>
                                <input type="password" name="current_password" class="form-control border-start-0 ps-0" placeholder="Required for changes" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                                    <i class="bi bi-shield-lock text-secondary"></i>
                                </span>
                                <input type="password" name="new_password" class="form-control border-start-0 ps-0" placeholder="Leave blank to keep same">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-bell me-2"></i>System Notifications</div>
                <div class="card-body">
                    <div class="list-group list-group-flush bg-transparent">
                        <div class="list-group-item bg-transparent border-secondary-subtle px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Booking Confirmations</div>
                                    <div class="small text-secondary">Receive an email when your booking is successfully processed.</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="confirm_booking" <?= $settings['confirm_booking'] ? "checked" : "" ?>>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent border-secondary-subtle px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Arrival Reminders</div>
                                    <div class="small text-secondary">Get notified 15 minutes before your scheduled arrival.</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="reminder_booking" <?= $settings['reminder_booking'] ? "checked" : "" ?>>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent border-0 px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Parking Expiry Alerts</div>
                                    <div class="small text-secondary">Critical alerts when your parking session is about to expire.</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="expiry_alert" <?= $settings['expiry_alert'] ? "checked" : "" ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary px-4 py-2 fw-bold">
                    <i class="bi bi-save me-2"></i>Save All Changes
                </button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-dashed bg-transparent" style="border: 2px dashed var(--glass-border);">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-check fs-1"></i>
                        </div>
                    </div>
                    <h5>Security Center</h5>
                    <p class="text-secondary small mb-4">Protecting your data with industry-standard encryption and security protocols.</p>
                    <div class="text-start w-100">
                        <div class="small mb-2"><i class="bi bi-check2 text-success me-2"></i>Two-Factor Auth Available</div>
                        <div class="small mb-2"><i class="bi bi-check2 text-success me-2"></i>Activity Monitoring</div>
                        <div class="small"><i class="bi bi-check2 text-success me-2"></i>Encrypted Transactions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

