</div> <!-- Close Content Area -->
</div> <!-- Close Wrapper if any -->

<!-- NEURAL ASSISTANT UI -->
<div id="aiTerminal" class="ai-terminal-collapsed transition-smooth">
    <div class="ai-header d-flex justify-content-between align-items-center" onclick="toggleAITerminal()">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-main bg-opacity-10 rounded-3">
                <i class="bi bi-robot text-main"></i>
            </div>
            <span class="small fw-bold text-white">FACILITY AI</span>
        </div>
        <i class="bi bi-chevron-up ai-toggle-icon"></i>
    </div>
    <div class="ai-body p-3">
        <div id="aiOutput" class="small text-secondary mb-3 overflow-auto" style="height: 200px;">
            <div class="mb-2 p-2 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                Hello. I am the Facility Intelligence node. How can I assist you with parking telemetry today?
            </div>
        </div>
        <div class="input-group">
            <input type="text" id="aiInput" class="form-control form-control-sm bg-dark border-secondary border-opacity-10 text-white" placeholder="Ask AI...">
            <button class="btn btn-sm btn-primary" onclick="processAI()"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<!-- VANGUARD COMMAND BAR -->
<div class="vanguard-command-bar transition-smooth backdrop-blur">
    <div class="d-flex align-items-center gap-3 px-4 py-2">
        <!-- Facility Overrides -->
        <div class="d-flex align-items-center gap-2 border-end border-secondary border-opacity-10 pe-4">
            <button id="evacBtn" class="btn btn-sm btn-outline-danger border-opacity-25 x-small fw-bold" onclick="toggleEvacuation()" title="Global Evacuation">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary border-opacity-25 x-small fw-bold" onclick="toggleFocusMode()" title="Operator Focus">
                <i class="bi bi-eye-slash-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary border-opacity-25 x-small fw-bold" onclick="toggleHologram()" title="Hologram Mode">
                <i class="bi bi-intersect"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning border-opacity-25 x-small fw-bold" onclick="toggleThermal()" title="Thermal Scan">
                <i class="bi bi-thermometer-high"></i>
            </button>
            <button id="soundToggle" class="btn btn-sm btn-outline-info border-opacity-25 x-small fw-bold" onclick="toggleSoundscape()" title="Facility Soundscape">
                <i class="bi bi-volume-up-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-info border-opacity-25 x-small fw-bold" onclick="cycleAtmosphere()" title="Atmospheric Override">
                <i class="bi bi-lightbulb-fill"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning border-opacity-25 x-small fw-bold" onclick="triggerIntercom()" title="Neural Intercom">
                <i class="bi bi-broadcast"></i>
            </button>
            <audio id="facilityAudio" loop src="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3"></audio>
            <!-- Tactical Sound Nodes -->
            <audio id="sfxHydraulic" src="https://assets.mixkit.co/sfx/preview/mixkit-sci-fi-door-62.mp3"></audio>
            <audio id="sfxLaser" src="https://assets.mixkit.co/sfx/preview/mixkit-laser-weapon-shot-1681.mp3"></audio>
            <audio id="sfxIntercom" src="https://assets.mixkit.co/sfx/preview/mixkit-intercom-alert-signal-2200.mp3"></audio>
        </div>

        <!-- Localization & Voice -->
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary border-0 x-small fw-bold dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-translate me-1"></i> EN/BN
                </button>
                <ul class="dropdown-menu dropdown-menu-dark border-secondary border-opacity-10 bg-titan shadow-lg">
                    <li><a class="dropdown-item x-small fw-bold py-2" href="#" onclick="setLanguage('en')">ENGLISH</a></li>
                    <li><a class="dropdown-item x-small fw-bold py-2" href="#" onclick="setLanguage('bn')">BENGALI</a></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-outline-primary border-0 x-small fw-bold" onclick="vocalizeStatus()" title="Vocalize Status">
                <i class="bi bi-megaphone-fill"></i>
            </button>
        </div>
    </div>
</div>

<!-- FACEID BIOMETRIC OVERLAY -->
<div id="faceIDOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-90 d-none z-max backdrop-blur-lg d-flex flex-column justify-content-center align-items-center">
    <div class="mb-4 position-relative">
        <i class="bi bi-person-bounding-box text-primary animate-pulse" style="font-size: 8rem;"></i>
        <div class="position-absolute top-0 start-0 w-100 h-100 border border-primary border-opacity-50 rounded-circle animate-spin" style="animation-duration: 2s;"></div>
    </div>
    <div class="text-primary fw-900 letter-spacing-5 mb-2">NEURAL BIOMETRIC SCAN</div>
    <div class="text-secondary x-small fw-bold">VERIFYING OPERATOR CLEARANCE...</div>
</div>

<!-- INTERCOM BROADCAST BAR -->
<div id="intercomBar" class="position-fixed top-0 start-0 w-100 bg-warning text-black fw-900 x-small py-2 text-center d-none z-max letter-spacing-2">
    [ALL-SECTOR BROADCAST] &nbsp; <span id="intercomMsg">INITIALIZING VOCAL NODE...</span>
</div>

<!-- Core Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Notification & Feature Hub -->
<script>
// --- AI TERMINAL LOGIC ---
function toggleAITerminal() {
    const term = document.getElementById('aiTerminal');
    term.classList.toggle('expanded');
}

function processAI() {
    const input = document.getElementById('aiInput');
    const out = document.getElementById('aiOutput');
    const q = input.value.trim().toLowerCase();
    if (!q) return;

    const userMsg = document.createElement('div');
    userMsg.className = 'mb-2 p-2 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-10 text-white small text-end';
    userMsg.innerText = q;
    out.appendChild(userMsg);
    input.value = '';

    // Simulated Intelligence Node
    setTimeout(() => {
        const aiMsg = document.createElement('div');
        aiMsg.className = 'mb-2 p-2 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-10 text-secondary small';
        
        let reply = "Processing request... Node response timed out. Please ask about 'rates', 'vip', or 'status'.";
        if (q.includes('rate')) reply = "Standard rates are $50/hr for Cars. VIP terminals are $100/hr.";
        if (q.includes('vip')) reply = "VIP nodes are located on Level 1, Section V. They feature extra-wide spacing and priority exit.";
        if (q.includes('status')) reply = "System heartbeats are nominal. All gates are currently operational.";
        
        aiMsg.innerText = reply;
        out.appendChild(aiMsg);
        out.scrollTop = out.scrollHeight;
    }, 800);
}

// --- NOTIFICATION ENGINE ---
async function fetchNotifications() {
    try {
        const response = await fetch('api_notifications.php');
        const data = await response.json();
        if (data.status === 'success') {
            const dot = document.getElementById('unreadCountDot');
            const list = document.getElementById('notifList');
            if (data.unread_count > 0) { dot.classList.remove('d-none'); } else { dot.classList.add('d-none'); }
            if (data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => `<li class="p-3 border-bottom border-secondary border-opacity-5 ${n.is_read ? 'opacity-50' : ''}"><div class="small text-white mb-1 fw-500">${n.message}</div><div class="x-small text-secondary fw-bold">${n.time}</div></li>`).join('');
            } else { list.innerHTML = `<li class="p-4 text-center text-secondary small">No system alerts detected.</li>`; }
        }
    } catch (err) {}
}

async function markNotificationsRead() {
    await fetch('api_notifications.php?mark_read=1');
    fetchNotifications();
}

// --- LUNAR SHIFT ENGINE ---
function applyLunarShift() {
    const hour = new Date().getHours();
    if (hour >= 19 || hour < 6) {
        document.body.style.filter = "brightness(0.9) contrast(1.1) saturate(0.9)";
        document.documentElement.style.setProperty('--glass-surface', 'rgba(2, 6, 23, 0.85)');
        console.log("Lunar Shift Active: Obsidian mode deepened.");
    }
}

// --- VANGUARD HEARTBEAT ---
function updateHeartbeat() {
    const logs = document.getElementById('systemLogs');
    if (logs) {
        const time = new Date().toLocaleTimeString();
        const msg = document.createElement('div');
        msg.innerText = `[${time}] System Heartbeat: NOMINAL`;
        logs.prepend(msg);
        if (logs.children.length > 20) logs.lastChild.remove();
    }
}

// --- LOCALIZATION ENGINE ---
function setLanguage(lang) {
    if(lang === 'bn') {
        alert("লোকালাইজেশন মোড সক্রিয়: Bengali language nodes are being initialized.");
        // Simple mock translation for key elements
        document.querySelectorAll('span, h2, h4, h5, p').forEach(el => {
           if(el.innerText === 'Command Center') el.innerText = 'কমান্ড সেন্টার';
           if(el.innerText === 'Node Overview') el.innerText = 'নোড ওভারভিউ';
        });
    } else {
        location.reload();
    }
}

// --- SIGNATURE ENGINES ---
function toggleHologram() {
    document.body.classList.toggle('hologram-active');
}

function toggleSoundscape() {
    const audio = document.getElementById('facilityAudio');
    const btn = document.getElementById('soundToggle');
    if (audio.paused) {
        audio.play();
        btn.classList.add('bg-info', 'text-white');
    } else {
        audio.pause();
        btn.classList.remove('bg-info', 'text-white');
    }
}

// --- ALPHA-OMEGA ENGINES ---
function toggleThermal() {
    document.body.classList.toggle('thermal-active');
}

function vocalizeStatus() {
    const msg = "Facility Status: Nominal. Occupancy at sixty eight percent. Eco savings cumulative at one hundred forty two kilograms of carbon. Storm Mode is currently standby.";
    const speech = new SpeechSynthesisUtterance(msg);
    speech.pitch = 0.8;
    speech.rate = 0.9;
    window.speechSynthesis.speak(speech);
}

// Storm Watcher Node
function checkStormMode() {
    const weather = document.body.innerText.toLowerCase();
    if (weather.includes('rain') || weather.includes('storm')) {
        document.body.classList.add('storm-mode');
        console.log("Storm Mode Triggered: Ambience shifted to Deep Sea Blue.");
    }
}

// --- ULTIMA ENGINES ---
function toggleEvacuation() {
    document.body.classList.toggle('evac-active');
    const btn = document.getElementById('evacBtn');
    if(document.body.classList.contains('evac-active')) {
        btn.classList.replace('btn-outline-danger', 'btn-danger');
        vocalizeStatus("EMERGENCY: Global Evacuation Protocol Engaged. Please follow exit telemetry.");
    } else {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
    }
}

function toggleFocusMode() {
    document.body.classList.toggle('focus-active');
}

// --- GRAND SENTINEL ENGINES ---
let currentAtmosphere = 0;
function cycleAtmosphere() {
    const root = document.documentElement;
    const colors = [
        { main: '#fbbf24', glow: '#f59e0b' }, // Obsidian Gold
        { main: '#f43f5e', glow: '#e11d48' }, // Tactical Red
        { main: '#10b981', glow: '#059669' }, // Titan Emerald
        { main: '#38bdf8', glow: '#0ea5e9' }  // Deep Sea Blue
    ];
    currentAtmosphere = (currentAtmosphere + 1) % colors.length;
    root.style.setProperty('--accent-main', colors[currentAtmosphere].main);
    root.style.setProperty('--accent-glow', colors[currentAtmosphere].glow);
    playSFX('laser');
}

function playSFX(type) {
    const audio = document.getElementById(type === 'laser' ? 'sfxLaser' : 'sfxHydraulic');
    audio.currentTime = 0;
    audio.play();
}

// --- APEX-PRIME ENGINES ---
function runFaceID(callback) {
    const overlay = document.getElementById('faceIDOverlay');
    overlay.classList.remove('d-none');
    playSFX('laser');
    setTimeout(() => {
        overlay.classList.add('d-none');
        if(callback) callback();
    }, 2500);
}

function triggerIntercom() {
    const msg = prompt("Enter broadcast message:");
    if(!msg) return;
    
    document.getElementById('sfxIntercom').play();
    const bar = document.getElementById('intercomBar');
    const msgNode = document.getElementById('intercomMsg');
    
    msgNode.innerText = msg.toUpperCase();
    bar.classList.remove('d-none');
    
    setTimeout(() => { bar.classList.add('d-none'); }, 8000);
}

applyLunarShift();
checkStormMode();
setInterval(updateHeartbeat, 5000);
fetchNotifications();
setInterval(fetchNotifications, 30000);
</script>

<style>
/* Neural Assistant Styles */
/* Vanguard Command Bar */
.vanguard-command-bar {
    position: fixed;
    bottom: 30px; left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    z-index: 9999;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.vanguard-command-bar .btn { border-radius: 50px; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; }

#aiTerminal {
    position: fixed;
    bottom: 30px; right: 30px;
    width: 300px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(30px);
    border: 1px solid rgba(251, 191, 36, 0.2);
    border-radius: 20px;
    z-index: 10000;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    height: 60px; /* Collapsed */
}
#aiTerminal.expanded { height: 350px; }
.ai-header { padding: 15px 20px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); }
.ai-toggle-icon { transition: transform 0.3s; }
#aiTerminal.expanded .ai-toggle-icon { transform: rotate(180deg); }
#aiOutput::-webkit-scrollbar { width: 4px; }
#aiOutput::-webkit-scrollbar-thumb { background: rgba(251, 191, 36, 0.2); border-radius: 10px; }
</style>

</body>
</html>
