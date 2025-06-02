// Carrega notificações ao entrar na página
document.addEventListener('DOMContentLoaded', () => {
    carregarNotificacoes();

    // Solicita permissão para notificações do sistema
    if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission().then(() => {});
    }
});

// WebSocket para notificações em tempo real
if (!window.socketNotificacoes) {
    window.socketNotificacoes = new WebSocket(`wss://wb.zabulonmarketing.com.br?token=ec877fe5f7a3562a97a7f1ed438ce377a07cd5ce`);

    window.socketNotificacoes.onopen = () => {
        console.log('✅ WebSocket de notificações conectado');
    };

    window.socketNotificacoes.onmessage = (event) => {
        const msg = JSON.parse(event.data);

        if (msg.evento === 'novaNotificacao') {
            const nova = msg.dados;

            const lista = document.getElementById('listaNotificacoes');
            const contador = document.getElementById('contadorNotificacoes');

            // Mostra notificação do sistema (estilo WhatsApp Web)
            if (Notification.permission === 'granted') {
                new Notification(nova.titulo, {
                    body: nova.mensagem,
                    icon: '/icone.png' // opcional
                });
            }

            // Renderiza a nova notificação
            const novaNotificacaoHTML = `
                <div class="relative group px-4 py-3 hover:bg-gray-50 transition flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-900">${nova.titulo}</p>
                        <p class="text-xs text-gray-500 mt-1">${nova.mensagem}</p>
                        <p class="text-[10px] text-gray-400 mt-1">${formatarTempo(nova.created_at)}</p>
                    </div>
                    <div class="flex flex-col gap-2 items-end ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="conversar?numero=${nova.link}" class="text-blue-600 hover:text-blue-800 text-sm" title="Visualizar">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button onclick="deletarNotificacao(${nova.id})"
                                class="text-red-500 hover:text-red-700 text-sm" title="Deletar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            if (lista) {
                lista.insertAdjacentHTML('afterbegin', novaNotificacaoHTML);
            }

            const atual = parseInt(contador?.textContent || '0');
            contador.textContent = atual + 1;
        }
    };
}

// Carregar notificações (manual e automático)
function carregarNotificacoes() {
        function formatarTempo(isoDate) {
        const data = new Date(isoDate);
        const agora = new Date();
        const diff = Math.floor((agora - data) / 1000);
        if (diff < 60) return `Há ${diff} segundos`;
        if (diff < 3600) return `Há ${Math.floor(diff / 60)} minutos`;
        if (diff < 86400) return `Há ${Math.floor(diff / 3600)} horas`;
        return `Há ${Math.floor(diff / 86400)} dia${Math.floor(diff / 86400) > 1 ? 's' : ''}`;
    }

    fetch('/notificacoes/listar')
        .then(res => res.json())
        .then(data => {
            const lista = document.getElementById('listaNotificacoes');
            const contador = document.getElementById('contadorNotificacoes');
            contador.textContent = data.length;

            if (data.length === 0) {
                lista.innerHTML = `<p class="p-4 text-gray-500 text-sm">Nenhuma notificação no momento.</p>`;
                return;
            }

            lista.innerHTML = data.map(n => {
                const linkVisualizar = n.tipo === 'reuniao_remarcada' || n.tipo === 'reuniao_agendada'
                    ? `<a href="conversar?numero=${n.link}" class="text-blue-600 hover:text-blue-800 text-sm" title="Visualizar">
                            <i class="bi bi-eye"></i>
                       </a>`
                    : `<button class="text-blue-600 hover:text-blue-800 text-sm" title="Visualizar">
                            <i class="bi bi-eye"></i>
                       </button>`;

                return `
                    <div class="relative group px-4 py-3 hover:bg-gray-50 transition flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-900">${n.titulo}</p>
                            <p class="text-xs text-gray-500 mt-1">${n.mensagem}</p>
                            <p class="text-[10px] text-gray-400 mt-1">${formatarTempo(n.created_at)}</p>
                        </div>
                        <div class="flex flex-col gap-2 items-end ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            ${linkVisualizar}
                            <button onclick="deletarNotificacao(${n.id})"
                                    class="text-red-500 hover:text-red-700 text-sm" title="Deletar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        });
}

// Marcar todas como lidas
function marcarTodasComoLidas() {
    fetch('/notificacoes/marcar-todas-como-lidas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(() => {
        carregarNotificacoes();
    });
}

// Deletar uma notificação
function deletarNotificacao(id) {
    fetch(`/notificacoes/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(() => {
        carregarNotificacoes();
    });
}

// Abrir/fechar dropdown
function toggleNotificacoes() {
    const modal = document.getElementById('modalNotificacoes');
    modal.classList.toggle('hidden');
    if (!modal.classList.contains('hidden')) {
        carregarNotificacoes();
    }
}

// Fechar dropdown ao clicar fora
document.addEventListener('click', function (event) {
    const wrapper = document.getElementById('notificacao-wrapper');
    const dropdown = document.getElementById('modalNotificacoes');
    if (!wrapper.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});


