@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Painel Kanban</h1>

    <div class="mb-4">
        <input type="text" id="novo-status-nome" placeholder="Nome do status" class="p-2 border rounded">
        <button onclick="adicionarStatus()" class="bg-blue-500 text-white px-3 py-1 rounded ml-2">Adicionar Status</button>
    </div>
    
    
    <div id="kanban-colunas" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @include('kanban._parcial') {{-- Carrega as colunas pela primeira vez --}}
    </div>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    function adicionarStatus() {
        const nome = document.getElementById('novo-status-nome').value;

        fetch('/kanban/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nome })
        }).then(() => {
            carregarKanban();
            document.getElementById('novo-status-nome').value = '';
        });
    }

    function removerStatus(id) {
        if (!confirm('Tem certeza que deseja excluir este status?')) return;

        fetch(`/kanban/status/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => carregarKanban());
    }

    function carregarKanban() {
        fetch("{{ route('kanban.parcial') }}")
            .then(res => res.text())
            .then(html => {
                document.getElementById("kanban-colunas").innerHTML = html;
                reativarSortable();
            });
    }

    function reativarSortable() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            new Sortable(column, {
                group: 'kanban',
                animation: 150,
                onEnd: function (evt) {
                    const id = evt.item.dataset.id;
                    const status = evt.to.id.replace('status-', '');

                    fetch('/kanban/atualizar-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id, status })
                    });
                }
            });
        });
    }


    reativarSortable();
    setInterval(carregarKanban, 5000);
</script>

