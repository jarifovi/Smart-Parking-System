        </main>
    </div> <!-- End Content Area -->

    <!-- Global Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 3D Tilt Interaction Engine
        document.addEventListener('mousemove', (e) => {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                if (x > 0 && x < rect.width && y > 0 && y < rect.height) {
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const rotateX = (y - centerY) / 20; // Subtle tilt
                    const rotateY = (centerX - x) / 20;
                    
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px) scale(1.02)`;
                    card.style.boxShadow = `0 30px 60px rgba(0,0,0,0.5), 0 0 20px rgba(56, 189, 248, 0.2)`;
                } else {
                    card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) scale(1)`;
                    card.style.boxShadow = `var(--card-shadow)`;
                }
            });
        });

        // Background Gravity Particles Drift
        document.addEventListener('mousemove', (e) => {
            const dots = document.querySelectorAll('.gravity-dot');
            const x = (window.innerWidth / 2 - e.clientX) / 50;
            const y = (window.innerHeight / 2 - e.clientY) / 50;
            dots.forEach(dot => {
                dot.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>
</body>
</html>
