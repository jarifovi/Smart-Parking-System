<?php
require_once 'config_database.php';
$sidebarKey = 'user_forge';
require_once 'helper_layout_user.php';
?>

<div class="mb-5">
    <h2 class="text-white fw-900 mb-2">VANGUARD ID FORGE</h2>
    <p class="text-secondary m-0">Generate your high-security digital access credentials.</p>
</div>

<div class="row g-5">
    <div class="col-lg-6">
        <div class="card p-5">
            <h5 class="text-white fw-bold mb-4">FORGE SETTINGS</h5>
            <div class="mb-4">
                <label class="form-label x-small fw-bold text-secondary">DISPLAY NAME</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($loggedUser['full_name']); ?>" readonly>
            </div>
            <div class="mb-4">
                <label class="form-label x-small fw-bold text-secondary">SECURITY RANK</label>
                <input type="text" class="form-control" value="Vanguard Level 1" readonly>
            </div>
            <div class="mb-5">
                <label class="form-label x-small fw-bold text-secondary">BADGE THEME</label>
                <select class="form-select bg-dark border-secondary border-opacity-10 text-white">
                    <option>Obsidian Gold (Default)</option>
                    <option>Neural Blue</option>
                    <option>Titan Emerald</option>
                </select>
            </div>
            <button class="btn btn-primary w-100 py-3 fw-bold" onclick="alert('Digital Pass Generated: Synchronizing with Wallet...')">
                INITIALIZE FORGE <i class="bi bi-fire ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Badge Preview Node -->
    <div class="col-lg-6 d-flex justify-content-center">
        <div class="vanguard-badge">
            <div class="badge-inner">
                <div class="badge-header d-flex justify-content-between align-items-center">
                    <div class="badge-logo">VANGUARD</div>
                    <div class="badge-rank">LVL-01</div>
                </div>
                <div class="badge-body text-center mt-4">
                    <div class="badge-avatar-frame mb-3">
                        <i class="bi bi-person-bounding-box" style="font-size: 4rem; color: var(--accent-main);"></i>
                    </div>
                    <h3 class="text-white fw-900 m-0"><?php echo strtoupper($loggedUser['full_name']); ?></h3>
                    <div class="x-small text-primary fw-bold letter-spacing-2 mt-1">CERTIFIED OPERATOR</div>
                </div>
                <div class="badge-footer mt-auto d-flex justify-content-between align-items-end">
                    <div class="badge-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=USER-<?php echo $loggedUser['id']; ?>&color=fbbf24&bgcolor=020617" alt="QR">
                    </div>
                    <div class="text-end">
                        <div class="x-small text-secondary fw-bold">NODE ACCESS</div>
                        <div class="small text-white fw-900">ALL SECTORS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.vanguard-badge {
    width: 320px;
    height: 480px;
    background: #020617;
    border: 2px solid var(--accent-main);
    border-radius: 20px;
    padding: 2px;
    box-shadow: 0 0 50px rgba(251, 191, 36, 0.2);
    position: relative;
    overflow: hidden;
}
.badge-inner {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #020617 0%, #0f172a 100%);
    border-radius: 18px;
    padding: 30px;
    display: flex;
    flex-direction: column;
}
.badge-logo { font-weight: 900; letter-spacing: 2px; color: #fff; font-size: 0.8rem; }
.badge-rank { color: var(--accent-main); font-weight: 900; font-size: 0.8rem; }
.badge-avatar-frame {
    width: 120px; height: 120px;
    margin: 0 auto;
    border: 2px solid rgba(251, 191, 36, 0.2);
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(251, 191, 36, 0.05);
}
.badge-qr img { border: 1px solid rgba(251, 191, 36, 0.2); padding: 5px; border-radius: 8px; }
</style>

<?php require_once 'helper_layout_footer.php'; ?>
