        let sidebarOpen = true; // Começa ABERTO

        window.onload = function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const logoSidebar = document.getElementById('logo-sidebar');
            const navLabel = document.getElementById('navegacao-label');

            sidebar.classList.add('w-64');
            sidebar.classList.remove('w-20');
            sidebarTexts.forEach(el => el.classList.remove('hidden'));
            logoSidebar.classList.remove('scale-75');
            navLabel.classList.remove('hidden');
        };

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const logoSidebar = document.getElementById('logo-sidebar');
            const navLabel = document.getElementById('navegacao-label');

            if (sidebarOpen) {
                // FECHAR
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                sidebarTexts.forEach(el => el.classList.add('hidden'));
                logoSidebar.classList.add('scale-75');
                navLabel.classList.add('hidden');
            } else {
                // ABRIR
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                sidebarTexts.forEach(el => el.classList.remove('hidden'));
                logoSidebar.classList.remove('scale-75');
                navLabel.classList.remove('hidden');
            }
            sidebarOpen = !sidebarOpen;
        }