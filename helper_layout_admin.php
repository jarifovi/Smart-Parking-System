<?php
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Dashboard'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css?v=9.0">
    <style>
        .gravity-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(circle at 50% 50%, #0f172a 0%, #020617 100%);
        }
    </style>
</head>
<body>
    <div class="gravity-bg"></div>
    <div class="gravity-dot" style="top: 10%; left: 15%; animation-duration: 8s;"></div>
    <div class="gravity-dot" style="top: 70%; left: 80%; animation-duration: 12s; animation-delay: -3s;"></div>
    <div class="gravity-dot" style="top: 40%; left: 90%; animation-duration: 10s; animation-delay: -5s; background: var(--accent-violet);"></div>
    
    <!-- Sidebar -->
    <div class="sidebar-fixed" id="sidebarMenu">
        <div class="text-center mb-5">
            <div class="d-inline-flex p-3 rounded-4 bg-primary bg-opacity-10 mb-3" style="border: 1px solid rgba(56, 189, 248, 0.3);">
                <i class="bi bi-p-circle-fill fs-2 text-info"></i>
            </div>
            <h4 class="fw-800 text-white m-0">SMART PARKING</h4>
            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 mt-2">ADMIN CORE v3.0</span>
        </div>

        <nav class="nav flex-column gap-2">
            <a href="admin_dashboard_home.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard Overview
            </a>
            <a href="admin_user_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_users' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i> Operator Control
            </a>
            <a href="admin_parking_spot_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_spots' ? 'active' : ''; ?>">
                <i class="bi bi-geo-fill"></i> Zone Management
            </a>
            <a href="admin_booking_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_bookings' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event-fill"></i> Active Sessions
            </a>
        </nav>

        <div class="mt-auto pt-5">
            <div class="card bg-dark bg-opacity-50 border-0 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-initials bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                        <?php echo $initials; ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="small fw-bold text-white text-truncate"><?php echo htmlspecialchars($loggedUser['full_name']); ?></div>
                        <div class="text-secondary small" style="font-size: 0.7rem;">Super Administrator</div>
                    </div>
                </div>
                <a href="auth_logout.php" class="btn btn-sm btn-outline-danger w-100 mt-3 border-opacity-25">Logout System</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <!-- Top System Bar -->
        <header class="d-flex justify-content-between align-items-center mb-5 p-3 rounded-4 bg-dark bg-opacity-25 border border-secondary border-opacity-10 backdrop-blur">
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-grow spinner-grow-sm text-info"></div>
                <span class="small text-secondary fw-bold">SYSTEM STATUS: <span class="text-success">SYNCHRONIZED</span></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative cursor-pointer">
                    <i class="bi bi-bell text-secondary fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px;"></span>
                </div>
                <div class="vr bg-secondary opacity-25" style="height: 20px;"></div>
                <div class="small text-white fw-bold d-none d-md-block"><?php echo htmlspecialchars($loggedUser['full_name']); ?></div>
            </div>
        </header>

        <main class="page-content" style="animation: slideInUp 0.8s ease-out;">
