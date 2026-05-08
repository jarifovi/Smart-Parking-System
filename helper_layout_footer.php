</div> <!-- Close Content Area -->
</div> <!-- Close Wrapper if any -->

<!-- Core Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Notification Hub JS -->
<script>
async function fetchNotifications() {
    try {
        const response = await fetch('api_notifications.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            const dot = document.getElementById('unreadCountDot');
            const list = document.getElementById('notifList');
            
            // Update Unread Dot
            if (data.unread_count > 0) {
                dot.classList.remove('d-none');
                dot.innerText = ''; // Pulsing effect
            } else {
                dot.classList.add('d-none');
            }
            
            // Update List
            if (data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => `
                    <li class="p-3 border-bottom border-secondary border-opacity-5 ${n.is_read ? 'opacity-50' : ''}">
                        <div class="small text-white mb-1 fw-500">${n.message}</div>
                        <div class="x-small text-secondary fw-bold">${n.time}</div>
                    </li>
                `).join('');
            } else {
                list.innerHTML = `<li class="p-4 text-center text-secondary small">No system alerts detected.</li>`;
            }
        }
    } catch (err) { console.error('Notification node failure:', err); }
}

async function markNotificationsRead() {
    await fetch('api_notifications.php?mark_read=1');
    fetchNotifications();
}

// Initial Sync & Heartbeat
fetchNotifications();
setInterval(fetchNotifications, 30000); // Check every 30s
</script>

<!-- Gravity Particle System -->
<script>
document.addEventListener('mousemove', (e) => {
    const dots = document.querySelectorAll('.gravity-dot');
    const x = e.clientX;
    const y = e.clientY;
    
    dots.forEach(dot => {
        const dx = x - dot.offsetLeft;
        const dy = y - dot.offsetTop;
        const dist = Math.sqrt(dx*dx + dy*dy);
        
        if (dist < 300) {
            dot.style.transform = `translate(${dx/15}px, ${dy/15}px)`;
        } else {
            dot.style.transform = `translate(0, 0)`;
        }
    });
});
</script>

</body>
</html>
