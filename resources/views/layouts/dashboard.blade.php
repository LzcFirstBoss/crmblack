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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-mart@5.4.0/css/emoji-mart.css" />
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@5.4.0/dist/browser.js"></script>
</head>
<style>
    /* Seta do dropdown */
    #modalNotificacoes::before {
        content: '';
        position: absolute;
        top: -8px;
        right: 24px;
        width: 14px;
        height: 14px;
        background: white;
        border-left: 1px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
        transform: rotate(45deg);
        z-index: 40;
    }

    /* Scroll mais suave */
    #listaNotificacoes::-webkit-scrollbar {
        width: 6px;
    }
    #listaNotificacoes::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 3px;
    }
</style>


<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-[#1F1F1F] text-white transition-all duration-300 ease-in-out w-64 overflow-y-auto shadow-xl flex flex-col justify-between">
            <!-- Topo / Logo e navegação -->
            <div>
                <div class="p-6 flex justify-center">
                    <img id="logo-sidebar" src="{{ asset('img/zabulon/logobrancalaranja.svg') }}" alt="Logo Zabulon"
                        class="h-12 transition-transform duration-300">
                </div>
                <div id="navegacao-label" class="px-6 text-xs text-gray-400 uppercase tracking-widest mb-2">
                    Navegação</div>
                <nav id="nav-links" class="space-y-1 px-4 text-sm font-medium">
                    <a href="{{ url('/dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('dashboard') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-house text-lg"></i> <span class="sidebar-text">Dashboard</span>
                    </a>
                    <a href="{{ url('/conversar') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('conversar') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-chat text-lg"></i> <span class="sidebar-text">Conversas</span>
                    </a>
                    <a href="{{ url('/kanban') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('kanban') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-funnel text-lg"></i> <span class="sidebar-text">CRM</span>
                    </a>
                    <a href="{{ url('/calendario') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('calendario') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-calendar-week text-lg"></i> <span class="sidebar-text">Calendário</span>
                    </a>
                    <a href="{{ url('/config') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('config') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-whatsapp text-lg"></i> <span class="sidebar-text">Configurações</span>
                    </a>
                                        <a href="{{ url('/disparo') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('disparo') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-send"></i> <span class="sidebar-text">Disparos</span>
                    </a>
                    </a>
                                        <a href="{{ url('/leads') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded transition {{ request()->is('disparo') ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-orange-600 hover:text-white' }}">
                        <i class="bi bi-people"></i> <span class="sidebar-text">Leads</span>
                    </a>
                </nav>
            </div>

            <!-- Rodapé / Botão sair -->
            <div class="p-4 border-t border-gray-700">
                <a href="{{ url('/logout') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded transition text-red-400 hover:bg-red-700 hover:text-white">
                    <i class="bi bi-box-arrow-right text-lg"></i> <span class="sidebar-text">Sair</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header class="relative p-4 flex justify-between items-center border-b"
        style="background: linear-gradient(135deg, #FE7F32, #e8671a);">
                <!-- Botão para ocultar/exibir o menu lateral -->
                <button onclick="toggleSidebar()" class="text-white">
                    <i class="bi bi-list text-2xl"></i>
                </button>

                <div class="flex items-center space-x-4">
                    <!-- Notificação -->
<div class="relative" id="notificacao-wrapper">
    <button onclick="toggleNotificacoes()" class="text-white hover:text-black relative focus:outline-none">
        <i class="bi bi-bell text-2xl"></i>
        <span id="contadorNotificacoes"
              class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-semibold rounded-full px-1.5 py-0.5">
            0
        </span>
    </button>

    <!-- Dropdown alinhado ao sino -->
    <div id="modalNotificacoes"
         class="hidden absolute top-full right-0 w-96 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
        <!-- Seta -->
        <div class="absolute -top-2 right-6 w-4 h-4 bg-white transform rotate-45 border-t border-l border-gray-200 z-40"></div>

        <!-- Cabeçalho -->
        <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800 text-sm">Notificações</h3>
            <button onclick="marcarTodasComoLidas()"
                    class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                Marcar todas como lidas
            </button>
        </div>

        <!-- Lista -->
        <div id="listaNotificacoes" class="max-h-96 overflow-y-auto bg-white divide-y divide-gray-100">
            <!-- Conteúdo via JS -->
        </div>
    </div>
</div>



                    <span class="text-white font-medium">Olá, {{ auth()->user()->name }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=orange&color=fff"
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

</body>
</html>
<!-- Lottie -->
<script src="{{ asset('js/notificacao/notificacao.js') }}"></script>
<script src="{{ asset('js/menu/menu.js') }}"></script>
<script src="https://unpkg.com/lottie-web@5.7.4/build/player/lottie.min.js"></script>