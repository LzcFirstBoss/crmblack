<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zabulon - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" href="{{ asset('img/zabulon/logopretalaranja.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-[#1F1F1F] text-white transition-all duration-300 ease-in-out w-20 overflow-y-auto shadow-xl flex flex-col justify-between">
            <!-- Topo / Logo e navegação -->
            <div>
                <div class="p-6 flex justify-center">
                    <img id="logo-sidebar" src="{{ asset('img/zabulon/logobrancalaranja.svg') }}" alt="Logo Zabulon"
                        class="h-12 hover:scale-105 transition-transform duration-300 scale-75">
                </div>
                <div id="navegacao-label" class="px-6 text-xs text-gray-400 uppercase tracking-widest mb-2 hidden">
                    Navegação</div>
                <nav id="nav-links" class="space-y-1 px-4 text-sm font-medium">
                    <a href="{{ url('/dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('dashboard') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-house text-lg"></i> <span class="sidebar-text hidden">Dashboard</span>
                    </a>
                    <a href="{{ url('/conversar') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('conversas') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-chat text-lg"></i> <span class="sidebar-text hidden">Conversas</span>
                    </a>
                    <a href="{{ url('/kanban') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('kanban') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-funnel text-lg"></i> <span class="sidebar-text hidden">CRM</span>
                    </a>
                    <a href="{{ url('/calendario') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('calendario') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-calendar-week text-lg"></i> <span class="sidebar-text hidden">Calendário</span>
                    </a>
                    <a href="{{ url('/config') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('config') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-whatsapp text-lg"></i> <span class="sidebar-text hidden">Configurações</span>
                    </a>
                </nav>
            </div>

            <!-- Rodapé / Botão sair -->
            <div class="p-4 border-t border-gray-700">
                <a href="{{ url('/logout') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded transition text-red-400 hover:bg-red-700 hover:text-white">
                    <i class="bi bi-box-arrow-right text-lg"></i> <span class="sidebar-text hidden">Sair</span>
                </a>
            </div>
        </aside>




        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header class="p-4 flex justify-between items-center border-b"
                style="background: linear-gradient(135deg, #FE7F32, #e8671a);">
                <!-- Botão para ocultar/exibir o menu lateral -->
                <button onclick="toggleSidebar()" class="text-white">
                    <i class="bi bi-list text-2xl"></i>
                </button>

                <div class="flex items-center space-x-4">
                    <!-- Notificação -->
                    <div class="relative">
                        <button class="text-white hover:text-black">
                            <i class="bi bi-bell text-2xl"></i>
                        </button>
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">
                            0
                        </span>
                    </div>

                    <span class="text-white font-medium">Olá, {{ auth()->user()->name }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}o&background=orange&color=fff"
                        class="w-8 h-8 rounded-full" alt="Avatar">
                </div>

            </header>

            <!-- Content Area -->
            <main class="flex-1 bg-grey overflow-y-auto">
                @yield('content')
                @livewireStyles

                @livewireScripts
            </main>
        </div>
    </div>

    <!-- Script para alternar exibição do sidebar -->
    <script>
        let sidebarOpen = false; // Começa fechado!

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const logoSidebar = document.getElementById('logo-sidebar');
            const navLabel = document.getElementById('navegacao-label');

            if (sidebarOpen) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');

                sidebarTexts.forEach(el => el.classList.add('hidden'));
                logoSidebar.classList.add('scale-75');
                navLabel.classList.add('hidden');
            } else {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');

                sidebarTexts.forEach(el => el.classList.remove('hidden'));
                logoSidebar.classList.remove('scale-75');
                navLabel.classList.remove('hidden');
            }
            sidebarOpen = !sidebarOpen;
        }
    </script>
    <!--  script do Lottie -->
    <script src="https://unpkg.com/lottie-web@5.7.4/build/player/lottie.min.js"></script>
</body>

</html>
