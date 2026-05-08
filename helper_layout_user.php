<?php
// helper_layout_user.php
require_once 'helper_authentication.php';
requireLogin();
$loggedUser = getLoggedUser($databaseConnection);

$initials = '';
if (!empty($loggedUser['full_name'])) {
    $parts = explode(' ', $loggedUser['full_name']);
    $initials = strtoupper(
        substr($parts[0], 0, 1) .
        (isset($parts[1]) ? substr($parts[1], 0, 1) : '')
    );
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Smart Parking – User'); ?></title>
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
                <p>&copy; ${new Date().getFullYear()} <span class="code-tag">Smart Parking System</span></p>
                <p>Designed & Developed with <i class="bi bi-heart-fill text-danger"></i> by <a href="#">Jarif Ovi</a></p>
                <div class="mt-2">
                    <span class="badge bg-info mx-1">PHP 8.2</span>
                    <span class="badge bg-info mx-1">MySQL</span>
                    <span class="badge bg-info mx-1">Bootstrap 5</span>
                </div>
            </div>
        `;
        pageContent.appendChild(footer);
    }
});
</script>

<!-- NAVBAR (fixed for all sidebar pages) -->
<nav class="navbar navbar-expand-lg navbar-dark sp-navbar fixed-top">
    <div class="container-fluid">
        <button class="mobile-toggle" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
            <span class="navbar-brand mb-0">Smart Parking</span>
            <span class="brand-pill">User Panel</span>
        </div>

        <div class="d-flex align-items-center ms-auto gap-1">
            <!-- Notifications icon -->
            <div class="dropdown">
                <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <?php
                    // Count unread notifications
                    $unreadCount = 0;
                    if (!empty($loggedUser['id'])) {
                        $stmtCount = $databaseConnection->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = 0");
                        $stmtCount->bind_param("i", $loggedUser['id']);
                        $stmtCount->execute();
                        $resCount = $stmtCount->get_result()->fetch_row();
                        $unreadCount = $resCount[0] ?? 0;
                    }
                    if ($unreadCount > 0):
                    ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0 border-0 shadow-lg">
                    <div class="dropdown-header border-bottom border-secondary-subtle py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Notifications</span>
                        <span class="badge bg-primary"><?php echo $unreadCount; ?> New</span>
                    </div>
                    <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                        <?php
                        if (!empty($loggedUser['id'])) {
                            $stmtNotif = $databaseConnection->prepare("SELECT * FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                            $stmtNotif->bind_param("i", $loggedUser['id']);
                            $stmtNotif->execute();
                            $resNotif = $stmtNotif->get_result();
                            if ($resNotif->num_rows > 0) {
                                while ($n = $resNotif->fetch_assoc()) {
                                    $icon = $n['is_read'] ? 'bi-check2-circle text-secondary' : 'bi-dot text-primary fs-3';
                                    echo "
                                    <a class='dropdown-item p-3 border-bottom border-secondary-subtle d-flex align-items-start gap-3' href='#'>
                                        <div class='mt-1'><i class='bi {$icon}'></i></div>
                                        <div>
                                            <div class='small text-white opacity-75'>".htmlspecialchars($n['message'])."</div>
                                            <div class='text-secondary mt-1' style='font-size: 0.7rem;'>".date('M d, H:i', strtotime($n['created_at']))."</div>
                                        </div>
                                    </a>";
                                }
                            } else {
                                echo "<div class='p-4 text-center text-secondary small'>No notifications yet.</div>";
                            }
                        }
                        ?>
                    </div>
                    <div class="p-2 text-center border-top border-secondary-subtle">
                        <a href="user_notifications.php" class="small text-primary text-decoration-none fw-bold">View All Activity</a>
                    </div>
                </div>
            </div>

            <!-- Profile dropdown -->
            <div class="dropdown">
                <button class="icon-button dropdown-toggle p-0" data-bs-toggle="dropdown" type="button">
                    <span class="avatar-pill">
                        <span class="avatar-initials">
                            <?php echo htmlspecialchars($initials ?: 'U'); ?>
                        </span>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($loggedUser['full_name'] ?? 'User'); ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="user_profile_settings.php">
                            <i class="bi bi-person-gear me-2"></i>Edit Profile
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Logout -->
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
    <div class="sidebar-section-title">User Menu</div>

    <a href="user_dashboard_home.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_dashboard' ? 'active' : ''; ?>">
        <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
    </a>

    <a href="user_booking_new.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_book_new' ? 'active' : ''; ?>">
        <i class="bi bi-parking"></i><span>Book Parking Spot</span>
    </a>

    <a href="user_vehicles.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_vehicles' ? 'active' : ''; ?>">
        <i class="bi bi-car-front"></i><span>My Vehicles</span>
    </a>

    <a href="user_bookings_list.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_bookings' ? 'active' : ''; ?>">
        <i class="bi bi-calendar-check"></i><span>My Bookings</span>
    </a>

    <a href="user_booking_history.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_history' ? 'active' : ''; ?>">
        <i class="bi bi-clock-history"></i><span>Booking History</span>
    </a>

    <a href="user_profile_settings.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_settings' ? 'active' : ''; ?>">
        <i class="bi bi-gear"></i><span>Settings</span>
    </a>

    <a href="user_support.php"
       class="sidebar-link <?php echo ($sidebarKey ?? '') === 'user_support' ? 'active' : ''; ?>">
        <i class="bi bi-headset"></i><span>Support & Help</span>
    </a>
</div>

<!-- MAIN CONTENT WRAPPER (everything after this is page-specific) -->
<div class="content-area">
    <main class="page-content">
