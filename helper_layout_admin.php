<?php
// helper_layout_admin.php
require_once 'helper_authentication.php';
requireAdmin();
$loggedUser = getLoggedUser($databaseConnection);
$initials = '';
if (!empty($loggedUser['full_name'])) {
    $parts = explode(' ', $loggedUser['full_name']);
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Smart Parking – Admin'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Global UI theme -->
    <link rel="stylesheet" href="ui_theme_main.css">
</head>
<body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarMenu = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebarMenu && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebarMenu.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebarMenu.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Add developer footer to the end of .page-content
    const pageContent = document.querySelector('.page-content');
    if (pageContent) {
        const footer = document.createElement('footer');
        footer.className = 'dev-footer';
        footer.innerHTML = `
            <div class="container">
                <p>&copy; ${new Date().getFullYear()} <span class="code-tag">Smart Parking Admin</span></p>
                <p>Engineered for Excellence by <a href="#">Jarif Ovi</a></p>
                <div class="mt-2">
                    <span class="badge bg-info mx-1">Admin Panel</span>
                    <span class="badge bg-info mx-1">Analytics Engine</span>
                    <span class="badge bg-info mx-1">Secure Auth</span>
                </div>
            </div>
        `;
        pageContent.appendChild(footer);
    }
});
</script>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sp-navbar fixed-top">
    <div class="container-fluid">
        <button class="mobile-toggle" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
            <span class="navbar-brand mb-0">Smart Parking</span>
            <span class="brand-pill">Admin Panel</span>
        </div>

        <div class="d-flex align-items-center ms-auto gap-1">
            <button class="icon-button" type="button">
                <i class="bi bi-bell"></i>
            </button>

            <div class="dropdown">
                <button class="icon-button dropdown-toggle p-0" data-bs-toggle="dropdown" type="button">
                    <span class="avatar-pill">
                        <span class="avatar-initials"><?php echo htmlspecialchars($initials ?: 'A'); ?></span>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($loggedUser['full_name'] ?? 'Admin'); ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="user_profile_settings.php"><i class="bi bi-person-gear me-2"></i>Edit Profile</a></li>
                </ul>
            </div>

            <a href="auth_logout.php" class="icon-button text-danger" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar-fixed" id="sidebarMenu">
    <div class="sidebar-section-title">Admin Menu</div>

    <a href="admin_dashboard_home.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_dashboard' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
    </a>

    <a href="admin_user_management.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_users' ? 'active' : ''; ?>">
        <i class="bi bi-people"></i><span>User Management</span>
    </a>

    <a href="admin_parking_spot_management.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_spots' ? 'active' : ''; ?>">
        <i class="bi bi-parking"></i><span>Parking Spots</span>
    </a>

    <a href="admin_booking_management.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_bookings' ? 'active' : ''; ?>">
        <i class="bi bi-journal-text"></i><span>Bookings</span>
    </a>

    <a href="admin_payment_management.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_payments' ? 'active' : ''; ?>">
        <i class="bi bi-credit-card"></i><span>Payments</span>
    </a>

    <a href="admin_report_analytics.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_reports' ? 'active' : ''; ?>">
        <i class="bi bi-graph-up-arrow"></i><span>Reports &amp; Analytics</span>
    </a>
</div>

<!-- MAIN CONTENT WRAPPER (everything after this is page-specific) -->
<div class="content-area">
    <main class="page-content">
