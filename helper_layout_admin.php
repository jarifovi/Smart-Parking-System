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
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#fbbf24">
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
            <h4 class="m-0 fw-900 text-white">Smart <span class="text-primary">Parking</span></h4>
            <div class="x-small text-secondary fw-bold mt-1" style="letter-spacing: 1px; font-size: 0.6rem;">ADMIN CONTROL</div>
        </div>

        <nav class="flex-grow-1">
            <a href="admin_dashboard_home.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-cpu-fill"></i>
                <span>Command Center</span>
            </a>
            <a href="admin_user_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_users' ? 'active' : ''; ?>">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Operators</span>
            </a>
            <a href="admin_parking_spot_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_spots' ? 'active' : ''; ?>">
                <i class="bi bi-layers-half"></i>
                <span>Zone Grid</span>
            </a>
            <a href="admin_booking_management.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_bookings' ? 'active' : ''; ?>">
                <i class="bi bi-activity"></i>
                <span>Live Sessions</span>
            </a>
            <a href="admin_anpr_scanner.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_anpr' ? 'active' : ''; ?>">
                <i class="bi bi-radar"></i>
                <span>Neural Scanner</span>
            </a>
            <a href="admin_report_analytics.php" class="sidebar-link <?php echo ($sidebarKey ?? '') === 'admin_reports' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-steps"></i>
                <span>Neural Reports</span>
            </a>
            <a href="#" class="sidebar-link text-warning opacity-75" onclick="toggleGodMode()">
                <i class="bi bi-terminal-fill"></i>
                <span>Tactical Overrides</span>
            </a>
        </nav>

        <div class="mt-auto pt-3 border-top border-secondary border-opacity-10 d-flex flex-column gap-2">
            <button class="btn btn-sm btn-outline-info border-opacity-25 x-small fw-bold py-2" onclick="toggleMessenger()">
                <i class="bi bi-chat-dots-fill me-2"></i> MESSENGER
            </button>
            <a href="auth_logout.php" class="btn btn-sm btn-outline-danger border-opacity-25 x-small fw-bold py-2">
                <i class="bi bi-power me-2"></i> TERMINATE SESSION
            </a>
        </div>
    </div>

    <!-- Messenger Node -->
    <div id="vanguardMessenger" class="position-fixed bottom-0 end-0 m-4 shadow-lg d-none" style="width: 300px; z-index: 10000;">
        <div class="card border-info border-opacity-25 bg-titan backdrop-blur p-0 overflow-hidden">
            <div class="p-3 bg-info bg-opacity-10 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                <div class="x-small fw-bold text-info"><i class="bi bi-broadcast me-2"></i>TACTICAL COMMS</div>
                <button class="btn-close btn-close-white small" onclick="toggleMessenger()"></button>
            </div>
            <div id="messengerFeed" class="p-3 overflow-y-auto" style="height: 200px; font-size: 0.75rem;">
                <div class="mb-2"><span class="text-info fw-bold">[SYS]:</span> Secure channel established.</div>
                <div class="mb-2"><span class="text-info fw-bold">[OVI]:</span> Node 04 reporting high density.</div>
            </div>
            <div class="p-2 border-top border-secondary border-opacity-10">
                <input type="text" class="form-control form-control-sm border-0 bg-transparent x-small" placeholder="Transmit priority message...">
            </div>
        </div>
    </div>
    
    <!-- God-Mode Tactical Terminal -->
    <div id="godTerminal" class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-95 d-none z-max backdrop-blur-lg p-5">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-primary border-opacity-25 pb-3">
            <div class="text-primary fw-900 letter-spacing-2"><i class="bi bi-terminal-fill me-2"></i>VANGUARD OS: GOD-MODE</div>
            <button class="btn-close btn-close-white" onclick="toggleGodMode()"></button>
        </div>
        <div id="terminalOutput" class="text-success small fw-mono overflow-y-auto mb-4" style="height: calc(100% - 150px); font-family: 'Courier New', Courier, monospace;">
            <div>[SYS]: Initialize Vanguard Tactical Override... SUCCESS</div>
            <div>[SYS]: Awaiting operator command...</div>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-primary">vanguard@sp-fos:~$</span>
            <input type="text" id="terminalInput" class="form-control bg-transparent border-0 text-white fw-mono shadow-none" placeholder="Execute command..." autofocus onkeydown="executeCommand(event)">
        </div>
    </div>

    <script>
        function toggleGodMode() {
            const term = document.getElementById('godTerminal');
            term.classList.toggle('d-none');
            if (!term.classList.contains('d-none')) {
                document.getElementById('terminalInput').focus();
            }
        }

        function executeCommand(e) {
            if (e.key === 'Enter') {
                const input = e.target;
                const out = document.getElementById('terminalOutput');
                const cmd = input.value.trim().toLowerCase();
                
                const line = document.createElement('div');
                line.innerHTML = `<span class="text-primary">vanguard@sp-fos:~$</span> ${cmd}`;
                out.appendChild(line);
                
                const resp = document.createElement('div');
                resp.className = 'text-warning ms-3 mb-2';
                
                if (cmd === '/gate-open-all') resp.innerText = "[OK] All facility gates initialized for priority entry.";
                else if (cmd === '/lockdown') resp.innerText = "[ALERT] GLOBAL LOCKDOWN PROTOCOL ENGAGED.";
                else if (cmd === '/purge-logs') resp.innerText = "[OK] System activity ledger sanitized.";
                else if (cmd === 'help') resp.innerText = "Available: /gate-open-all, /lockdown, /purge-logs, /clear";
                else if (cmd === '/clear') { out.innerHTML = ''; resp.innerText = ''; }
                else resp.innerText = "[ERR] Command unknown. Access denied.";
                
                out.appendChild(resp);
                input.value = '';
                out.scrollTop = out.scrollHeight;
            }
        }
    </script>

    <!-- Main Content -->
    <div class="content-area p-0">
        <!-- Global Revenue Ticker -->
        <div class="bg-dark bg-opacity-50 border-bottom border-secondary border-opacity-10 py-2 overflow-hidden position-relative">
            <div class="ticker-wrap">
                <div class="ticker-content x-small fw-bold text-primary letter-spacing-1">
                    [LIVE FEED] SECTOR ALPHA: 84% OCCUPANCY &nbsp;&nbsp; | &nbsp;&nbsp; [REV] GROSS SESSION TOTAL: $14,240.00 &nbsp;&nbsp; | &nbsp;&nbsp; [ALERT] PEAK DEMAND DETECTED IN SECTOR B &nbsp;&nbsp; | &nbsp;&nbsp; [ECO] 142KG CO2 SAVED TODAY &nbsp;&nbsp; | &nbsp;&nbsp; [SYS] ALL NODES SYNCHRONIZED
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <!-- Sector Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary border-opacity-25 fw-900 dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-building-fill me-2"></i> SECTOR ALPHA (HQ)
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark border-secondary border-opacity-10 bg-titan shadow-lg">
                        <li><a class="dropdown-item x-small fw-bold py-2" href="#">SECTOR ALPHA (HQ)</a></li>
                        <li><a class="dropdown-item x-small fw-bold py-2" href="#">SECTOR BETA (SATELLITE)</a></li>
                        <li><a class="dropdown-item x-small fw-bold py-2 text-secondary" href="#">+ INITIALIZE NEW SECTOR</a></li>
                    </ul>
                </div>
                <div class="text-end x-small text-secondary fw-bold">
                    <i class="bi bi-hdd-network me-1"></i> FACILITY NODE: SP-CORE-742
                </div>
            </div>
        <header class="d-flex justify-content-between align-items-center mb-5 p-3 rounded-4 bg-dark bg-opacity-25 border border-secondary border-opacity-10 backdrop-blur">
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-grow spinner-grow-sm text-info"></div>
                <span class="small text-secondary fw-bold">SYSTEM STATUS: <span class="text-success">SYNCHRONIZED</span></span>
            </div>
            <div class="d-flex align-items-center gap-4">
                <!-- Weather Telemetry -->
                <div class="d-flex align-items-center gap-2 bg-dark bg-opacity-50 px-3 py-1 rounded-pill border border-secondary border-opacity-10">
                    <i class="bi bi-cloud-sun text-info"></i>
                    <span class="x-small fw-bold text-secondary">28°C / RAIN ALERT</span>
                </div>
                
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
