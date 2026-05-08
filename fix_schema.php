<?php
require_once 'config_database.php';

// --- ELITE SCHEMA UPGRADE ---
$queries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS loyalty_points INT DEFAULT 0",
    "ALTER TABLE parking_spots ADD COLUMN IF NOT EXISTS is_ev_ready TINYINT(1) DEFAULT 0"
];

echo "<div style='background:#020617; color:#fbbf24; padding:2rem; font-family:sans-serif;'>";
echo "<h3>🛰️ NEURAL SCHEMA SYNC</h3>";

foreach ($queries as $q) {
    if ($databaseConnection->query($q)) {
        echo "<p style='color:#10b981;'>[SUCCESS] Query executed: $q</p>";
    } else {
        echo "<p style='color:#f43f5e;'>[FAILED] " . $databaseConnection->error . "</p>";
    }
}

echo "<p>System optimized for EV Infrastructure and Loyalty Telemetry.</p>";
echo "</div>";
?>
