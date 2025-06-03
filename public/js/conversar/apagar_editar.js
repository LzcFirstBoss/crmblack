let mensagemRespondida = null;

function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        document.querySelectorAll('.dropdown-options').forEach(d => {
            if (d !== dropdown) d.classList.add('hidden');
        });
        dropdown.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.group')) {
            document.querySelectorAll('.dropdown-options').forEach(d => d.classList.add('hidden'));
        }
    });

    function responderMensagem(id) {
        console.log('Responder ID:', id);
        // Abrir campo de resposta
    }

    function editarMensagem(id, texto) {
        console.log('Editar:', id, texto);
        // Abrir modal de edição
    }

    function apagarMensagem(id) {
        console.log('Apagar:', id);
        // Confirmar e deletar
    }

    function apagarMensagem(id) {
    const numero = numeroAtualSelecionado;

    if (!numero) {
        alert('Número não definido!');
        return;
    }

    if (!confirm('Tem certeza que deseja apagar esta mensagem para todos?')) return;

    fetch(window.ROTA_APAGAR_MENSAGEM, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            id: id,
            remoteJid: numero + '@s.whatsapp.net',
            fromMe: true
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            carregarNovasMensagens();
        } else {
            alert('Erro ao apagar: ' + (data.detalhes || 'Erro desconhecido'));
        }
    })
    .catch(erro => {
        alert('Erro na requisição: ' + erro.message);
    });
}

function editarMensagem(id, texto) {
    document.getElementById('inputEditarMensagem').value = texto;
    document.getElementById('idMensagemEditar').value = id;
    document.getElementById('modalEditarMensagem').classList.remove('hidden');
    document.getElementById('inputEditarMensagem').focus();
}

function fecharModalEditar() {
    document.getElementById('modalEditarMensagem').classList.add('hidden');
}

function confirmarEditarMensagem() {
    const id = document.getElementById('idMensagemEditar').value;
    const novoTexto = document.getElementById('inputEditarMensagem').value.trim();

    if (!novoTexto) return alert('Digite algo para atualizar.');

    const numeroCompleto = numeroAtualSelecionado + '@s.whatsapp.net';

    fetch('/mensagem/editar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: JSON.stringify({
            number: numeroAtualSelecionado,
            text: novoTexto,
            remoteJid: numeroCompleto,
            fromMe: true,
            id: id
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            fecharModalEditar();
            carregarNovasMensagens();
        } else {
            alert('Erro ao editar: ' + (data.mensagem || 'Erro desconhecido'));
        }
    });
}

function responderMensagem(idMensagem) {
    // Busca dados da mensagem na DOM
    const msgEl = document.querySelector(`[data-idmsg='${idMensagem}']`);
    if (!msgEl) return;

    let texto = msgEl.dataset.texto || 'Mensagem';
    const numero = msgEl.dataset.numero || '';
    const fromMe = msgEl.dataset.fromme === 'true';

    // Detecta se é mídia
    const lowerTexto = texto.toLowerCase();

    if (/\.(jpg|jpeg|png|gif|webp)$/i.test(lowerTexto)) {
        texto = '[Imagem]';
    } else if (/\.(mp3|ogg|wav|m4a|webm)$/i.test(lowerTexto)) {
        texto = '[Áudio]';
    } else if (/\.(mp4|webm|mov)$/i.test(lowerTexto)) {
        texto = '[Vídeo]';
    } else if (/\.(pdf|doc|docx|txt|xls|xlsx|zip|rar|csv)$/i.test(lowerTexto)) {
        texto = '[Documento]';
    }

    const idWhatsapp = msgEl.dataset.idwhatsapp || null;


    mensagemRespondida = {
        id: idMensagem,
        idWhatsapp: idWhatsapp, // <- ESSENCIAL PARA O QUOTED FUNCIONAR NO WHATSAPP
        texto: texto,
        numero: numero,
        fromMe: fromMe
    };

    // Exibe o bloco visual de resposta
    document.getElementById('respostaAtiva').classList.remove('hidden');
    document.getElementById('respostaTexto').textContent = texto;
    document.getElementById('respostaRemetente').textContent = fromMe ? 'Você' : 'Cliente';
}


function cancelarResposta() {
    mensagemRespondida = null;
    document.getElementById('respostaAtiva').classList.add('hidden');
}

function scrollParaMensagem(id) {
    const el = document.getElementById('mensagem-' + id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('destacar-msg');

        setTimeout(() => {
            el.classList.remove('destacar-msg');
        }, 1500);
    }
}

