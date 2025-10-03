<?php
// footer.php
?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        // Toggle sidebar em dispositivos móveis
        function toggleSidebar() {
            sidebar.classList.toggle('mobile-show');
            sidebarOverlay.classList.toggle('show');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        // Fechar sidebar ao clicar no overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        // Fechar sidebar ao clicar em um link (mobile)
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });

        // Animações de entrada
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
            card.classList.add('fade-in');
        });

        // Redimensionamento da janela
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('mobile-show');
                sidebarOverlay.classList.remove('show');
            }
        });

        // Adicionar classe active baseada na URL atual
        function setActiveNav() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        }

        setActiveNav();
    });

    // Função para confirmar ações
    function confirmAction(message) {
        return confirm(message || 'Tem certeza que deseja realizar esta ação?');
    }
    </script>
  </body>
</html>