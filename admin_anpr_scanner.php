<?php
require_once 'config_database.php';
$sidebarKey = 'admin_anpr';
require_once 'helper_layout_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="text-white fw-900 mb-2">NEURAL PLATE RECOGNITION</h2>
        <p class="text-secondary m-0">Synthesizing vehicle telemetry into operator-grade profiles.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-2 px-3 fw-bold">ANPR NODE: ACTIVE</span>
    </div>
</div>

<div class="row g-4">
    <!-- Scanner Hub -->
    <div class="col-lg-8">
        <div class="card p-0 overflow-hidden border-primary border-opacity-10">
            <div id="scanOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center z-2 d-none" style="background: rgba(0,0,0,0.8); backdrop-filter: blur(10px);">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <div class="text-primary fw-900 letter-spacing-2 animate-pulse">NEURAL SCAN IN PROGRESS...</div>
            </div>
            <div class="p-5 text-center bg-dark bg-opacity-25" style="min-height: 400px; border: 2px dashed rgba(251, 191, 36, 0.1);">
                <i class="bi bi-camera-fill text-secondary opacity-25" style="font-size: 5rem;"></i>
                <h4 class="text-white mt-4 fw-bold">INITIALIZE OPTICAL SCAN</h4>
                <p class="text-secondary small">Point facility camera or upload vehicle image for identification.</p>
                <button class="btn btn-primary px-5 py-3 mt-4" onclick="runNeuralScan()">
                    START SCANNER <i class="bi bi-radar ms-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Node -->
    <div id="scanResult" class="col-lg-4 d-none">
        <div class="card h-100 border-success border-opacity-25">
            <div class="text-success small fw-bold mb-3"><i class="bi bi-check-circle-fill me-2"></i>IDENTIFICATION SUCCESS</div>
            <div class="text-center mb-4">
                <div class="bg-dark rounded-3 p-3 mb-3 border border-secondary border-opacity-10">
                    <h1 class="text-white fw-900 letter-spacing-5 m-0">DHAKA-1234</h1>
                </div>
                <span class="x-small text-secondary fw-bold">EXTRACTED PLATE ID</span>
            </div>
            <hr class="border-secondary border-opacity-10">
            <div class="mb-4">
                <div class="small text-secondary fw-bold mb-2">NETWORK IDENTITY</div>
                <div class="text-white fw-bold">JARIF OVI</div>
                <div class="x-small text-secondary">Verified Operator</div>
            </div>
            <div class="mb-4">
                <div class="small text-secondary fw-bold mb-2">VEHICLE TELEMETRY</div>
                <div class="text-white fw-bold">TESLA MODEL S</div>
                <div class="x-small text-secondary">Vanguard-Alpha / Obsidian Black</div>
            </div>
            <div class="mt-auto">
                <button class="btn btn-outline-success w-100 x-small fw-bold py-2">GRANT NODE ACCESS</button>
            </div>
        </div>
    </div>
</div>

<script>
function runNeuralScan() {
    const overlay = document.getElementById('scanOverlay');
    const result = document.getElementById('scanResult');
    
    overlay.classList.remove('d-none');
    
    setTimeout(() => {
        overlay.classList.add('d-none');
        result.classList.remove('d-none');
        
        // Notify System
        const speech = new SpeechSynthesisUtterance("Vehicle identified: Dhaka one two three four. Operator Jarif Ovi confirmed.");
        speech.pitch = 0.8;
        window.speechSynthesis.speak(speech);
    }, 3000);
}
</script>

<?php require_once 'helper_layout_footer.php'; ?>
