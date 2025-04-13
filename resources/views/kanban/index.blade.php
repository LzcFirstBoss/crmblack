@extends('layouts.dashboard')
<title>Zabulon - CRM</title>
@section('content')

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

    <div class="mb-4">
        <button onclick="abrirModalNovaLista()" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
            + Nova Lista
        </button>             
    </div>

    <div id="kanban-colunas" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @include('kanban._parcial')
    </div>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function abrirModalNovaLista() {
        const conteudo = `
            <input type="text" id="novo-status-nome" placeholder="Nome da lista" class="w-full p-2 border rounded mb-4">
            <label class="block text-sm text-gray-600 mb-1">Cor da Lista:</label>
            <input type="color" id="nova-cor" value="#ffffff" class="w-12 h-12 border rounded cursor-pointer">
        `;
    
        abrirModal("Criar Nova Lista", conteudo, [
            {
                text: 'Cancelar',
                classes: 'px-4 py-2 bg-gray-300 text-black rounded',
                action: fecharModal
            },
            {
                text: 'Criar',
                classes: 'px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700',
                action: criarLista
            }
        ]);
    }
    
    function criarLista() {
    const nome = document.getElementById('novo-status-nome')?.value;
    const cor = document.getElementById('nova-cor')?.value;

    if (!nome) return abrirModal("Erro", "Digite o nome da lista.");

    fetch('/kanban/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nome, cor })
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) {
            const msg = data.errors?.nome?.[0] || "Erro ao adicionar.";
            abrirModal("Erro", msg);
            return;
        }

        fecharModal();
        carregarKanban();
    });
}

    
    function removerStatus(id) {
        abrirModal("Tem certeza que deseja excluir esta lista?", '', [
            {
                text: 'Cancelar',
                classes: 'px-4 py-2 bg-gray-300 text-black rounded',
                action: fecharModal
            },
            {
                text: 'Confirmar',
                classes: 'px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700',
                action: () => {
                    fetch(`/kanban/status/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(res => {
                        if (!res.ok) {
                            abrirModal("Erro", "Não foi possível excluir.");
                            return;
                        }
    
                        fecharModal();
                        carregarKanban();
                    });
                }
            }
        ]);
    }
    
    function abrirModal(titulo, conteudoHTML = '', botoes = []) {
    document.getElementById('modal-title').textContent = titulo;
    document.getElementById('modal-content').innerHTML = conteudoHTML;

    const btnContainer = document.getElementById('modal-buttons');
    btnContainer.innerHTML = '';

    if (botoes.length === 0) {
        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Fechar';
        closeBtn.className = 'px-4 py-2 bg-gray-300 rounded';
        closeBtn.onclick = fecharModal;
        btnContainer.appendChild(closeBtn);
    } else {
        botoes.forEach(btn => {
            const el = document.createElement('button');
            el.textContent = btn.text;
            el.className = btn.classes;
            el.onclick = () => {
                btn.action(); // <- chama a função
                fecharModal(); // <- fecha o modal
            };
            btnContainer.appendChild(el);
        });
    }

    document.getElementById('modal').classList.remove('hidden');
}

    
    function fecharModal() {
        document.getElementById('modal').classList.add('hidden');
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
    
@endsection
