<?php
require_once 'helper_authentication.php';
requireLogin();
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
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#fbbf24">
    <title><?php echo htmlspecialchars($pageTitle ?? 'User Portal'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css?v=9.0">
</head>
<body>
    <div class="gravity-bg"></div>
    <div class="gravity-dot" style="top: 15%; left: 20%; animation-duration: 9s;"></div>
    <div class="gravity-dot" style="top: 80%; left: 75%; animation-duration: 11s; animation-delay: -2s;"></div>
    <div class="gravity-dot" style="top: 30%; left: 85%; animation-duration: 13s; animation-delay: -4s; background: var(--accent-violet);"></div>
    
    <!-- Sidebar -->
    <div class="sidebar-fixed">
        <div class="sidebar-brand">
            <h4 class="m-0 fw-900 text-white">Smart <span class="text-primary">Parking</span></h4>
            <div class="x-small text-secondary fw-bold mt-1" style="letter-spacing: 1px; font-size: 0.6rem;">SYSTEM CORE</div>
        </div>

        <nav class="flex-grow-1">
            <a href="user_dashboard_home.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-box-fill"></i>
                <span>Node Overview</span>
            </a>
            <a href="user_booking_new.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_book_new' ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle-dotted"></i>
                <span>Initialize Booking</span>
            </a>
            <a href="user_bookings_list.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_bookings' ? 'active' : ''; ?>">
                <i class="bi bi-journal-code"></i>
                <span>Session Ledger</span>
            </a>
            <a href="user_vehicles.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_vehicles' ? 'active' : ''; ?>">
                <i class="bi bi-car-front"></i>
                <span>Fleet Telemetry</span>
            </a>
            <a href="user_id_forge.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_forge' ? 'active' : ''; ?>">
                <i class="bi bi-person-badge-fill"></i>
                <span>Access Forge</span>
            </a>
        </nav>

        <div class="mt-auto pt-3 border-top border-secondary border-opacity-10">
            <a href="auth_logout.php" class="btn btn-sm btn-outline-danger w-100 border-opacity-25 x-small fw-bold py-2">
                <i class="bi bi-power me-2"></i> TERMINATE SESSION
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <header class="d-flex justify-content-between align-items-center mb-5 p-3 rounded-4 bg-dark bg-opacity-25 border border-secondary border-opacity-10 backdrop-blur">
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-grow spinner-grow-sm text-info"></div>
                <span class="small text-secondary fw-bold">ZONE STATUS: <span class="text-success">GATES OPEN</span></span>
            </div>
            <div class="d-flex align-items-center gap-4">
                <!-- Weather Telemetry -->
                <div class="d-flex align-items-center gap-2 bg-dark bg-opacity-50 px-3 py-1 rounded-pill border border-secondary border-opacity-10">
                    <i class="bi bi-cloud-sun text-info"></i>
                    <span class="x-small fw-bold text-secondary">28°C / HUMID</span>
                </div>

                <!-- Notification Node -->
                <div class="dropdown">
                    <div class="position-relative cursor-pointer" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell text-secondary fs-5"></i>
                        <span id="unreadCountDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none" style="width: 10px; height: 10px;"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg mt-3" aria-labelledby="notifDropdown" style="width: 320px; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 20px;">
                        <li class="p-3 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white small">USER NOTIFICATIONS</span>
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
