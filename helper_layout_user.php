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
    <title><?php echo htmlspecialchars($pageTitle ?? 'User Portal'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="ui_theme_main.css">
</head>
<body>
    <div class="gravity-bg"></div>
    <div class="gravity-dot" style="top: 15%; left: 20%; animation-duration: 9s;"></div>
    <div class="gravity-dot" style="top: 80%; left: 75%; animation-duration: 11s; animation-delay: -2s;"></div>
    <div class="gravity-dot" style="top: 30%; left: 85%; animation-duration: 13s; animation-delay: -4s; background: var(--accent-violet);"></div>
    
    <!-- Sidebar -->
    <div class="sidebar-fixed" id="sidebarMenu">
        <div class="text-center mb-5">
            <div class="d-inline-flex p-3 rounded-4 bg-primary bg-opacity-10 mb-3" style="border: 1px solid rgba(56, 189, 248, 0.3);">
                <i class="bi bi-p-circle-fill fs-2 text-info"></i>
            </div>
            <h4 class="fw-800 text-white m-0">SP PORTAL</h4>
            <span class="badge bg-primary bg-opacity-10 text-info border border-primary border-opacity-25 mt-2">DASHBOARD v3.0</span>
        </div>

        <nav class="nav flex-column gap-2">
            <a href="user_dashboard_home.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Portal Overview
            </a>
            <a href="user_booking_new.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_book_new' ? 'active' : ''; ?>">
                <i class="bi bi-plus-square-fill"></i> New Reservation
            </a>
            <a href="user_bookings_list.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_bookings' ? 'active' : ''; ?>">
                <i class="bi bi-list-stars"></i> My Sessions
            </a>
            <a href="user_vehicles.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_vehicles' ? 'active' : ''; ?>">
                <i class="bi bi-car-front-fill"></i> My Fleet
            </a>
            <a href="user_support.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_support' ? 'active' : ''; ?>">
                <i class="bi bi-headset"></i> Support Hub
            </a>
        </nav>

        <div class="mt-auto pt-5">
            <div class="card bg-dark bg-opacity-50 border-0 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                        <?php echo $initials; ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="small fw-bold text-white text-truncate"><?php echo htmlspecialchars($loggedUser['full_name']); ?></div>
                        <div class="text-secondary small" style="font-size: 0.7rem;">Member ID: #<?php echo $loggedUser['id']; ?></div>
                    </div>
                </div>
                <a href="auth_logout.php" class="btn btn-sm btn-outline-danger w-100 mt-3 border-opacity-25">Exit Portal</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <main class="page-content" style="animation: slideInUp 0.8s ease-out;">
