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
    <div class="sidebar-fixed">
        <div class="sidebar-brand">
            <h4>SP CORE <small class="text-mute fs-6 opacity-50">v3.0</small></h4>
            <div class="small text-info opacity-75 fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">ADMIN COMMAND NODE</div>
        </div>

        <nav class="flex-grow-1">
            <a href="admin_dashboard_home.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard Overview</span>
            </a>
            <a href="admin_user_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_users' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>Operator Control</span>
            </a>
            <a href="admin_parking_spot_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_spots' ? 'active' : ''; ?>">
                <i class="bi bi-geo-fill"></i>
                <span>Zone Management</span>
            </a>
            <a href="admin_booking_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_bookings' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event-fill"></i>
                <span>Active Sessions</span>
            </a>
            <a href="admin_report_analytics.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_reports' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Deep Analytics</span>
            </a>
        </nav>

        <div class="mt-auto pt-4 border-top border-secondary border-opacity-10">
            <div class="d-flex align-items-center gap-3 mb-3 px-2">
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate"><?php echo htmlspecialchars($loggedUser['full_name']); ?></div>
                    <div class="text-mute x-small opacity-50">Super Administrator</div>
                </div>
            </div>
            <a href="auth_logout.php" class="btn btn-outline-danger w-100 py-2 border-opacity-25 x-small fw-bold">
                LOGOUT SYSTEM
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <header class="d-flex justify-content-between align-items-center mb-5 p-3 rounded-4 bg-dark bg-opacity-25 border border-secondary border-opacity-10 backdrop-blur">
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-grow spinner-grow-sm text-info"></div>
                <span class="small text-secondary fw-bold">SYSTEM STATUS: <span class="text-success">SYNCHRONIZED</span></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Node -->
                <div class="dropdown">
                    <div class="position-relative cursor-pointer" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell text-secondary fs-5"></i>
                        <span id="unreadCountDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none" style="width: 10px; height: 10px;"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg mt-3" aria-labelledby="notifDropdown" style="width: 320px; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 20px;">
                        <li class="p-3 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white small">TELEMETRY ALERTS</span>
                            <a href="javascript:void(0)" onclick="markNotificationsRead()" class="x-small text-info text-decoration-none fw-bold">CLEAR ALL</a>
                        </li>
                        <div id="notifList" class="overflow-auto" style="max-height: 350px;">
                            <li class="p-4 text-center text-secondary small">Scanning for alerts...</li>
                        </div>
                    </ul>
                </div>
                <div class="vr bg-secondary opacity-25" style="height: 20px;"></div>
                <div class="small text-white fw-bold d-none d-md-block"><?php echo htmlspecialchars($loggedUser['full_name']); ?></div>
            </div>
        </header>

        <main class="page-content" style="animation: slideInUp 0.8s ease-out;">
