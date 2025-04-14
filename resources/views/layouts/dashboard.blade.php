<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zabulon - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" href="{{ asset('img/zabulon/logopretalaranja.svg') }}">
</head>
<body class="bg-gray-100 text-gray-900">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="bg-black text-white transition-all duration-300 ease-in-out w-64 overflow-hidden">
        <div class="p-4">
            <img src="{{ asset('img/zabulon/logobrancalaranja.svg') }}" alt="Logo Zabulon" class="h-12 mx-auto">
        </div>          
        <nav class="mt-4 space-y-1 px-4">
            <a href="{{ '/dashboard' }}" class="block px-3 py-2 rounded hover:bg-orange-600"><i class="bi bi-house"></i> Dashboard</a>
            <a href="" class="block px-3 py-2 rounded hover:bg-orange-600"><i class="bi bi-chat"></i> Conversas</a>
            <a href="{{ '/kanban' }}" class="block px-3 py-2 rounded hover:bg-orange-600"><i class="bi bi-funnel"></i> CRM</a>
            <a href="#" class="block px-3 py-2 rounded hover:bg-orange-600"><i class="bi bi-calendar-week"></i> Calendário</a>
            <a href="{{ '/logout' }}" class="block px-3 py-2 rounded hover:bg-orange-600"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
        <header class="p-4 flex justify-between items-center border-b" style="background: linear-gradient(135deg, #FE7F32, #e8671a);">
            <!-- Botão para ocultar/exibir o menu lateral -->
            <button onclick="toggleSidebar()" class="text-white">
                <i class="bi bi-list text-2xl"></i>
            </button>
            
            <div class="flex items-center space-x-3">
                <span class="text-white font-medium">Olá, {{ auth()->user()->name }}</span>
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}o&background=orange&color=fff" class="w-8 h-8 rounded-full" alt="Avatar">
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 bg-grey overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

<!-- Script para alternar exibição do sidebar -->
<script>
    let sidebarOpen = true;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebarOpen) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
        } else {
            sidebar.classList.remove('w-0');
            sidebar.classList.add('w-64');
        }
        sidebarOpen = !sidebarOpen;
    }
</script>
</body>
</html>
