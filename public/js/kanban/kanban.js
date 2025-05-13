const socket = new WebSocket(`ws://localhost:3000?token=ec877fe5f7a3562a97a7f1ed438ce377a07cd5ce`);

socket.onopen = () => console.log("Conectado ao WebSocket");

socket.onmessage = (event) => {
    const mensagem = JSON.parse(event.data);

    if (mensagem.evento === 'kanban:zerarNotificacao') {
    const card = document.querySelector(`.kanban-card[data-numero="${mensagem.dados.numero}"]`);
    if (card) {
        const bolinha = card.querySelector('.notificacao-bolinha');
        if (bolinha) bolinha.remove();
    }

    const contato = document.querySelector(`#contato-${mensagem.dados.numero} .notificacao-bolinha`);
    if (contato) contato.remove();
}


    if (mensagem.evento === 'kanban:moverCard') {
        const id = mensagem.dados.id;
        const status = mensagem.dados.status;

        const card = document.querySelector(`[data-id="${id}"]`);
        const colunaDestino = document.getElementById(`status-${status}`);

        if (card && colunaDestino) {
            colunaDestino.appendChild(card);
        }
    }

    if (mensagem.evento === 'kanban:atualizar') {
        carregarKanban();
    }

    if (mensagem.evento === 'kanban:novaMensagem') {
        const dados = mensagem.dados;
        const cardExistente = document.querySelector(`.kanban-card[data-numero="${dados.numero}"]`);
        const coluna = document.getElementById(`status-${dados.status_id}`);

        const remetente = dados.bot
            ? 'Bot'
            : dados.enviado_por_mim
                ? dados.usuario ? `(${dados.usuario})` : 'Usuário'
                : 'Cliente';

        let preview = dados.conteudo;

        if (/\.(jpg|jpeg|png|gif)$/i.test(preview)) {
            preview = '<i class="bi bi-card-image"></i> Imagem';
        } else if (/\.(mp3|ogg|wav)$/i.test(preview)) {
            preview = '<i class="bi bi-music-note-beamed"></i> Áudio';
        } else if (/\.(mp4|mov|avi)$/i.test(preview)) {
            preview = '<i class="bi bi-camera-reels"></i> Vídeo';
        } else if (/\.(pdf|docx?|xlsx?)$/i.test(preview)) {
            preview = '<i class="bi bi-file-earmark-text"></i> Documento';
        } else {
            preview = preview.length > 100 ? preview.slice(0, 100) + '...' : preview;
        }

const bolinhaHTML = (qtd) => `
    <span class="notificacao-bolinha ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
        ${qtd}
    </span>`;

        if (cardExistente) {
            cardExistente.querySelector('.kanban-card-body').innerHTML = `<strong>${remetente}:</strong> ${preview}`;
            cardExistente.querySelector('.kanban-card-footer').textContent = dados.created_at;

            // Atualiza a bolinha, se existir
            const bolinhaContainer = cardExistente.querySelector(`#bolinha-${dados.numero}`);

            if (bolinhaContainer) {
                if (dados.mensagens_novas > 0) {
                    bolinhaContainer.innerHTML = `
                        <span class="notificacao-bolinha ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                            ${dados.mensagens_novas}
                        </span>`;
                } else {
                    bolinhaContainer.innerHTML = '';
                }
            }


        } else if (coluna) {
            const novoCard = document.createElement('div');
            novoCard.className = 'kanban-card';
            novoCard.dataset.id = dados.id;
            novoCard.dataset.numero = dados.numero;

            novoCard.innerHTML = `
                <div class="kanban-card-header">
                    <div class="flex items-center justify-between">
                        <a href="/conversar?numero=${dados.numero}" class="hover:underline text-orange-600 text-xs font-semibold" target="_blank">
                            <i class="bi bi-whatsapp"></i> ${dados.numero}
                        </a>
                                        <div id="bolinha-${dados.numero}">
                    ${dados.mensagens_novas > 0 ? bolinhaHTML(dados.mensagens_novas) : ''}
                </div>
                    </div>
                </div>
                <div class="kanban-card-body">
                    <strong>${remetente}:</strong> ${preview}
                </div>
                <div class="kanban-card-footer text-right text-xs text-gray-400 mt-2">
                    ${dados.created_at}
                </div>
            `;

            coluna.prepend(novoCard);
        }
    }
};


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
            'X-CSRF-TOKEN': window.csrfToken // ← defina isso no layout se estiver fora do Blade
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

        setTimeout(() => {
            fetch('http://localhost:3001/enviar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ evento: 'kanban:atualizar', dados: {} })
            });
        }, 300);
    });
}

function atualizarCor(id, cor) {
    fetch(`/kanban/status/${id}/cor`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ cor })
    }).then(() => {
        const coluna = document.querySelector(`#status-${id}`).parentElement;
        coluna.style.backgroundColor = cor + "20";
        coluna.style.borderTopColor = cor;

        setTimeout(() => {
            fetch('http://localhost:3001/enviar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ evento: 'kanban:atualizar', dados: {} })
            });
        }, 300);
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
                    headers: { 'X-CSRF-TOKEN': window.csrfToken }
                }).then(res => {
                    if (!res.ok) {
                        abrirModal("Erro", "Não foi possível excluir.");
                        return;
                    }

                    fecharModal();
                    carregarKanban();

                    setTimeout(() => {
                        fetch('http://localhost:3001/enviar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ evento: 'kanban:atualizar', dados: {} })
                        });
                    }, 300);
                });
            }
        }
    ]);
}

function carregarKanban() {
    fetch("/kanban/parcial")
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
            onEnd: function(evt) {
                const id = evt.item.dataset.id;
                const status = evt.to.id.replace('status-', '');

                fetch('/kanban/atualizar-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ id, status })
                });

                socket.send(JSON.stringify({
                    evento: 'kanban:moverCard',
                    dados: { id, status }
                }));
            }
        });
    });
}

document.getElementById('filtro-funil').addEventListener('input', function () {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll('#kanban-colunas > div').forEach(coluna => {
        const titulo = coluna.querySelector('h2')?.innerText.toLowerCase() || '';
        coluna.style.display = titulo.includes(filtro) ? '' : 'none';
    });
});

reativarSortable();

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
            el.type = 'button'; // ← evita submit acidental
            el.textContent = btn.text;
            el.className = btn.classes;
            el.onclick = () => {
                btn.action();
                fecharModal();
            };
            btnContainer.appendChild(el);
        });
    }

    document.getElementById('modal').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('modal').classList.add('hidden');
}
