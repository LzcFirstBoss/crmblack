@extends('layouts.dashboard')
<title>Zabulon - CRM</title>
@section('content')
    <style>
        .color-picker {
            -webkit-appearance: none;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 0 0 1px #ccc;
        }

        .color-picker::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .color-picker::-webkit-color-swatch {
            border: none;
            border-radius: 50%;
        }

        .kanban-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            cursor: grab;
            word-wrap: break-word;
            overflow-wrap: break-word;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInCard 0.4s forwards;
        }

        @keyframes fadeInCard {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
    </style>
    <!-- Modal Reutilizável -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded shadow max-w-md w-full">
            <h2 id="modal-title" class="text-lg font-semibold text-gray-800 mb-4"></h2>
            <div id="modal-content" class="mb-4"></div>
            <div class="flex justify-end space-x-2" id="modal-buttons"></div>
        </div>
    </div>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6"><i class="bi bi-funnel-fill"></i> CRM - Zabulon</h1>
        <div class="mb-6 flex items-center space-x-4">
            <button onclick="abrirModalNovaLista()"
                class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 whitespace-nowrap">
                + Nova Lista
            </button>

            <input type="text" id="filtro-funil" placeholder="Pesquisar funil..."
                class="w-64 p-3 border border-gray-300 rounded focus:outline-none focus:ring focus:border-orange-400">
        </div>


        <!-- KANBAN COLUNAS -->
        <div id="kanban-colunas" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @include('kanban._parcial')
        </div>
    </div>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    window.csrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('js/kanban/kanban.js') }}"></script>
@endsection
