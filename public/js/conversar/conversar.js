let socket = null;
let numeroAtualSelecionado = null;
let mediaRecorder = null;
let audioChunks = [];
let gravando = false;
let pausado = false;
let cancelado = false;
let tempoGravado = 0;
let intervaloTempo = null;

// Verificar se veio número pela URL
const urlParams = new URLSearchParams(window.location.search);
const numeroSelecionadoInicial = urlParams.get('numero');

if (numeroSelecionadoInicial) {
    abrirConversa(numeroSelecionadoInicial);
}


// Conectar WebSocket
function conectarWebSocket() {
    const token = window.WEBSOCKET_TOKEN;
    socket = new WebSocket(`wss://wb.zabulonmarketing.com.br?token=${token}`);

    socket.onopen = () => console.log('Conectado ao WebSocket');
    socket.onmessage = (event) => {
        const mensagem = JSON.parse(event.data);
        if (mensagem.evento === 'kanban:novaMensagem' && mensagem.dados.numero === numeroAtualSelecionado) {
            carregarNovasMensagens();
        }
    };
}

conectarWebSocket();

function abrirConversa(numero) {
    numeroAtualSelecionado = numero;

    document.getElementById('titulo-contato').innerHTML = `
    <div class="flex items-center gap-3">
        <span class="text-lg font-bold">${numero}</span>
        <button id="botToggle" class="flex items-center gap-2 px-3 py-1 rounded-full text-white text-xs bg-gray-400 shadow-sm hover:brightness-90 transition">
            Carregando...
        </button>
    </div>
`;


    // Buscar status atual do bot
    fetch('/api/bot/status/' + numero)
        .then(response => response.json())
        .then(data => atualizarBotButton(data.botativo));

    document.getElementById('area-input').classList.remove('hidden');
    document.getElementById('mensagem-inicial').classList.add('hidden');
    document.getElementById('mensagens-chat').classList.remove('hidden');

    document.querySelectorAll('.contato').forEach(c => c.classList.remove('bg-gray-200'));
    document.getElementById('contato-' + numero) ?.classList.add('bg-gray-200');

    document.getElementById('mensagens-chat').innerHTML = '<div class="w-full flex justify-center items-center py-10 text-gray-400"><i class="bi bi-arrow-repeat animate-spin text-2xl mr-2"></i> Carregando mensagens...</div>';

    fetch('/conversar/' + numero)
        .then(res => res.text())
        .then(html => {
            document.getElementById('mensagens-chat').innerHTML = html;
            document.getElementById('chat-mensagens') ?.scrollTo(0, document.getElementById('chat-mensagens').scrollHeight);
        });

    fetch('/zerar-mensagens-novas/' + numero, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN }
    }).then(atualizarListaContatos);
}

function atualizarBotButton(ativo) {
    const botButton = document.getElementById('botToggle');
    botButton.classList.remove('bg-red-600', 'bg-green-600');
    botButton.classList.add(ativo ? 'bg-red-600' : 'bg-green-600');
    botButton.innerHTML = `<i class="bi bi-power text-sm"></i> ${ativo ? 'Desligar Bot' : 'Ligar Bot'}`;

    botButton.onclick = () => {
        botButton.disabled = true;
        botButton.innerHTML = '<i class="bi bi-hourglass-split animate-spin text-sm"></i> Atualizando...';

        fetch('/api/bot/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero: numeroAtualSelecionado })
            })
            .then(response => response.json())
            .then(data => {
                atualizarBotButton(data.botativo);
            });
    };
}



function carregarNovasMensagens() {
    fetch('/conversar/' + numeroAtualSelecionado)
        .then(res => res.text())
        .then(html => {
            document.getElementById('mensagens-chat').innerHTML = html;
            document.getElementById('chat-mensagens') ?.scrollTo(0, document.getElementById('chat-mensagens').scrollHeight);
        });
}

function atualizarListaContatos() {
    fetch('/conversar-parcial')
        .then(res => res.text())
        .then(html => {
            document.getElementById('lista-contatos-itens').innerHTML = html;

            // Marca visualmente o contato atual
            if (numeroAtualSelecionado) {
                const atual = document.getElementById('contato-' + numeroAtualSelecionado);
                if (atual) atual.classList.add('bg-gray-200');
            }

            // Reaplica filtros
            filtrarContatos();
            filtrarContatosPorStatus(filtroAtual);

            // Reabrir dropdown se estava aberto
            const dropdown = document.getElementById('dropdownFiltro');
            if (dropdownAberto && dropdown) {
                dropdown.classList.remove('hidden');
            }

            // Atualizar texto do botão de filtro
            const btnFiltro = document.querySelector('[onclick="toggleDropdownFiltro()"] i');
            if (btnFiltro) {
                let label = 'Filtro';
                if (filtroAtual === 'nao_lidas') label = 'Não lidas';
                else if (filtroAtual === 'lidas') label = 'Lidas';
                else label = 'Todas';

                btnFiltro.parentElement.childNodes[1].nodeValue = ` ${label}`;
            }
        });
}




setInterval(atualizarListaContatos, 2000);

function enviarMensagem() {
    const mensagemInput = document.getElementById('input-mensagem');
    let mensagem = mensagemInput.innerHTML.trim();

    mensagem = mensagem
        .replace(/<div><br><\/div>/g, '\n')
        .replace(/<div>/g, '\n')
        .replace(/<\/div>/g, '')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/&nbsp;/g, ' ')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&')
        .replace(/<[^>]+>/g, '');

    if (!mensagem) return;

    // Resetar input
    mensagemInput.textContent = '';
    mensagemInput.focus();

    // Adiciona a mensagem na tela com status "pendente"
    const mensagensDiv = document.getElementById('mensagens-chat');
    const mensagemHTML = `
        <div class="flex justify-end mb-2">
            <div class="relative rounded-2xl text-[15px] font-normal leading-relaxed text-black px-4 min-w-[120px] w-fit" style="position: relative; background-color: #D9FDD3; padding-top: 6px; padding-bottom: 4px;">
                <span>${mensagem}</span>
                <small class="block text-xs text-gray-500">enviando...</small>
            </div>
        </div>
    `;
    mensagensDiv.insertAdjacentHTML('beforeend', mensagemHTML);
    document.getElementById('chat-mensagens') ?.scrollTo(0, mensagensDiv.scrollHeight);

    // Envia para a API
    const respostaParaEnviar = mensagemRespondida;
    cancelarResposta();

    fetch(window.ROTA_ENVIAR_MENSAGEM, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
          body: JSON.stringify({
                numero: numeroAtualSelecionado,
                mensagem: mensagem,
                resposta: respostaParaEnviar // <- usa a cópia
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'Mensagem enviada com sucesso') {
                carregarNovasMensagens();
            } else {
                alert(data.erro || 'Erro ao enviar a mensagem.');
            }
        });
}

// -------------------- ÁUDIO --------------------
const btnIniciarGravacao = document.getElementById('btnIniciarGravacao');
const btnPausarContinuarAudio = document.getElementById('btnPausarContinuarAudio');
const btnCancelarAudio = document.getElementById('btnCancelarAudio');
const btnEnviarAudio = document.getElementById('btnEnviarAudio');
const gravandoContainer = document.getElementById('gravandoContainer');
const barraAnimada = document.getElementById('barraAnimada');
const tempoGravadoEl = document.getElementById('tempoGravado');
const inputMensagem = document.getElementById('input-mensagem');
const btnEnviarTexto = document.getElementById('btnEnviarTexto');
const barraInput = document.getElementById('input-mensagem').parentElement;

// Alternar botão mic/send ao digitar
inputMensagem.addEventListener('input', () => {
    if (inputMensagem.value.trim() !== '') {
        btnEnviarTexto.classList.remove('hidden');
        btnIniciarGravacao.classList.add('hidden');
    } else {
        btnEnviarTexto.classList.add('hidden');
        btnIniciarGravacao.classList.remove('hidden');
    }
});

function formatarTempo(segundos) {
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    return `${m}:${s < 10 ? '0' + s : s}`;
}

function iniciarTimer() {
    clearInterval(intervaloTempo);
    intervaloTempo = setInterval(() => {
        if (!pausado) {
            tempoGravado++;
            tempoGravadoEl.textContent = formatarTempo(tempoGravado);
        }
    }, 1000);
}

function pararTimer() {
    clearInterval(intervaloTempo);
}

function resetUI() {
    gravando = false;
    pausado = false;
    cancelado = false;
    audioChunks = [];

    pararTimer();
    tempoGravado = 0;
    tempoGravadoEl.textContent = '0:00';

    barraInput.classList.remove('hidden');
    gravandoContainer.classList.add('hidden');

    btnPausarContinuarAudio.innerHTML = '<i class="bi bi-pause-fill"></i>';
    barraAnimada.style.animationPlayState = 'running';

    // Atualiza botão enviar/mic após a gravação
    if (inputMensagem.value.trim() !== '') {
        btnEnviarTexto.classList.remove('hidden');
        btnIniciarGravacao.classList.add('hidden');
    } else {
        btnEnviarTexto.classList.add('hidden');
        btnIniciarGravacao.classList.remove('hidden');
    }
}

function handleAudioStop() {
    pararTimer();

    if (cancelado) {
        resetUI();
        return;
    }

    const blob = new Blob(audioChunks, { type: 'audio/webm' });
    if (blob.size === 0) {
        resetUI();
        return;
    }

    converterParaBase64(blob, (base64) => {
        enviarAudioBase64(base64);
        resetUI();
    });
}

btnIniciarGravacao.addEventListener('click', () => {
    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.start();

        gravando = true;
        pausado = false;
        cancelado = false;
        audioChunks = [];

        barraInput.classList.add('hidden');
        gravandoContainer.classList.remove('hidden');

        tempoGravado = 0;
        tempoGravadoEl.textContent = '0:00';
        iniciarTimer();

        mediaRecorder.addEventListener('dataavailable', e => audioChunks.push(e.data));
        mediaRecorder.addEventListener('stop', handleAudioStop);
    });
});

btnPausarContinuarAudio.addEventListener('click', () => {
    if (!pausado) {
        mediaRecorder.pause();
        pausado = true;
        btnPausarContinuarAudio.innerHTML = '<i class="bi bi-play-fill"></i>';
        barraAnimada.style.animationPlayState = 'paused';
    } else {
        mediaRecorder.resume();
        pausado = false;
        btnPausarContinuarAudio.innerHTML = '<i class="bi bi-pause-fill"></i>';
        barraAnimada.style.animationPlayState = 'running';
    }
});

btnCancelarAudio.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        cancelado = true;
        mediaRecorder.stop();
    }
});

btnEnviarAudio.addEventListener('click', () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        cancelado = false;
        mediaRecorder.stop();
    }
});

function enviarAudioBase64(base64audio) {
    const numero = numeroAtualSelecionado.replace(/\D/g, '');

    fetch('/api/evolution/enviar-audio-base64', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: JSON.stringify({ numero: numero, audio_base64: base64audio })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'Áudio enviado com sucesso') carregarNovasMensagens();
            else alert(data.erro || 'Erro ao enviar áudio.');
        });
}

function converterParaBase64(blob, callback) {
    const reader = new FileReader();
    reader.readAsDataURL(blob);
    reader.onloadend = () => callback(reader.result.split(',')[1]);
}

// Botão + e menu de anexar
const btnAdicionar = document.getElementById('btnAdicionar');
const menuAnexar = document.getElementById('menuAnexar');

btnAdicionar.addEventListener('click', () => {
    if (menuAnexar.classList.contains('hidden')) {
        menuAnexar.classList.remove('hidden');
        btnAdicionar.classList.add('open');
    } else {
        menuAnexar.classList.add('hidden');
        btnAdicionar.classList.remove('open');
    }
});

// Fechar o menu ao clicar fora
document.addEventListener('click', (e) => {
    if (!btnAdicionar.contains(e.target) && !menuAnexar.contains(e.target)) {
        menuAnexar.classList.add('hidden');
        btnAdicionar.classList.remove('open');
    }
});

// Função para adicionar formatação no texto
function formatarTexto(simbolo) {
    const sel = window.getSelection();
    if (!sel.rangeCount) return;

    const range = sel.getRangeAt(0);
    const selectedText = range.toString();

    if (!selectedText) return;

    const newNode = document.createTextNode(`${simbolo}${selectedText}${simbolo}`);
    range.deleteContents();
    range.insertNode(newNode);
}

// Gerenciar a borda com base no conteúdo (scroll automático)
const inputMensagemDiv = document.getElementById('input-mensagem');
const barraInputContainer = inputMensagemDiv.parentElement;


// Função que atualiza a borda baseado no tamanho do conteúdo
function atualizarBorda() {
    if (inputMensagemDiv.scrollHeight > inputMensagemDiv.clientHeight) {
        barraInputContainer.classList.add('scrolling');
    } else {
        barraInputContainer.classList.remove('scrolling');
    }
}

inputMensagemDiv.addEventListener('input', atualizarBorda);
inputMensagemDiv.addEventListener('scroll', atualizarBorda);

atualizarBorda();


// Detectar digitação e mudança de conteúdo
inputMensagem.addEventListener('input', atualizarBorda);

// Detectar scroll (opcional, caso role manualmente também)
inputMensagem.addEventListener('scroll', atualizarBorda);

// Remover seleção indesejada quando clica fora do campo
document.addEventListener('click', (e) => {
    const ignorar = [
        '#input-mensagem',
        '#pesquisa-contato',
        '#legendaMidia',
        '#emojiPicker',
        '#btnEmoji',
        '#inputEditarMensagem'
    ];

    for (let seletor of ignorar) {
        if (e.target.closest(seletor)) return;
    }

    window.getSelection().removeAllRanges();
});


// Rodar ao carregar para verificar se já tem conteúdo
atualizarBorda();

// ------------------- ANEXOS -------------------

function handleMultiplosArquivos(event, tipo) {
    const files = Array.from(event.target.files);

    if (files.length > 5) {
        showToast('O número máximo de mídias por envio é 5.');
        event.target.value = ''; // limpa a seleção
        return;
    }

    arquivosSelecionados = files;
    abrirModalPreviewMultiplos(files);
}

document.getElementById('btnAnexarFotoVideo').addEventListener('click', () => document.getElementById('inputFotoVideo').click());
document.getElementById('btnAnexarAudio').addEventListener('click', () => document.getElementById('inputAudio').click());
document.getElementById('btnAnexarDocumento').addEventListener('click', () => document.getElementById('inputDocumento').click());

document.getElementById('inputFotoVideo').addEventListener('change', e => handleMultiplosArquivos(e, 'fotoVideo'));
document.getElementById('inputAudio').addEventListener('change', e => handleFile(e, 'audio'));
document.getElementById('inputDocumento').addEventListener('change', e => handleFile(e, 'document'));

let arquivosSelecionados = [];
let tipoSelecionado = null;
const modal = document.getElementById('modalPreview');
const previewMidia = document.getElementById('previewMidia');
const legendaMidia = document.getElementById('legendaMidia');
const btnFecharModal = document.getElementById('fecharModal');
const btnConfirmarEnvio = document.getElementById('confirmarEnvio');

function handleFile(event, tipo) {
    const file = event.target.files[0];
    if (!file) return;
    abrirModalPreview(file, tipo);
}

function removerArquivoSelecionado(index) {
    arquivosSelecionados.splice(index, 1);
    abrirModalPreviewMultiplos(arquivosSelecionados);
}

let midiasPreview = [];
let midiaPreviewIndex = 0;


function abrirModalPreviewMultiplos(files) {
    legendaMidia.value = '';
    previewMidia.innerHTML = '';
    document.getElementById('spinnerPreview').classList.remove('hidden');

    previewMidia.className = 'flex overflow-x-auto space-x-4 p-2 max-w-full';
    midiasPreview = arquivosSelecionados; // Atualiza lista para o modo visualização

    files.forEach((file, index) => {
        const url = URL.createObjectURL(file);
        const tipo = file.type.startsWith('image/') ? 'image'
                    : file.type.startsWith('video/') ? 'video'
                    : file.type.startsWith('audio/') ? 'audio'
                    : 'document';

        let elementoHTML = '';

        if (tipo === 'image') {
            elementoHTML = `<img src="${url}" class="h-28 w-28 object-cover rounded-lg">`;
        } else if (tipo === 'video') {
            elementoHTML = `<video src="${url}" class="h-28 w-28 object-cover rounded-lg" muted></video>`;
        } else if (tipo === 'audio') {
            elementoHTML = `
                <div class="w-28 h-28 flex flex-col items-center justify-center bg-gray-200 rounded-lg text-gray-600">
                    <i class="bi bi-mic-fill text-2xl mb-1"></i>
                    <span class="text-xs text-center">Áudio</span>
                </div>`;
        } else {
            elementoHTML = `
                <div class="w-28 h-28 flex flex-col items-center justify-center bg-gray-100 rounded-lg text-gray-600">
                    <i class="bi bi-file-earmark-text text-2xl mb-1"></i>
                    <span class="text-xs text-center break-words px-1">${file.name}</span>
                </div>`;
        }

        const card = document.createElement('div');
        card.className = "relative flex-shrink-0 cursor-pointer";
        card.setAttribute('data-index', index);
        card.onclick = () => abrirVisualizacaoIndividual(index);

        card.innerHTML = `
            <div class="relative shadow rounded-lg overflow-hidden group">
                ${elementoHTML}
                <button onclick="event.stopPropagation(); removerArquivoSelecionado(${index})"
                    class="absolute top-1 right-1 bg-white/80 hover:bg-red-500 hover:text-white text-gray-700 rounded-full p-1 transition text-xs">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;

        previewMidia.appendChild(card);
    });

    modal.classList.remove('hidden');
    legendaMidia.focus();

    setTimeout(() => {
        document.getElementById('spinnerPreview').classList.add('hidden');
    }, 200);
}

function abrirVisualizacaoIndividual(index) {
    midiaPreviewIndex = index;
    const file = arquivosSelecionados[index];
    const url = URL.createObjectURL(file);
    const tipo = file.type.startsWith('image/') ? 'image'
                : file.type.startsWith('video/') ? 'video'
                : file.type.startsWith('audio/') ? 'audio'
                : 'document';

    const content = document.getElementById('previewContent');
    content.innerHTML = '';

    let el;
    if (tipo === 'image') {
        el = document.createElement('img');
        el.src = url;
        el.className = 'max-h-[80vh] rounded-lg';
    } else if (tipo === 'video') {
        el = document.createElement('video');
        el.src = url;
        el.controls = true;
        el.className = 'max-h-[80vh] rounded-lg bg-black';
    } else if (tipo === 'audio') {
        el = document.createElement('audio');
        el.src = url;
        el.controls = true;
        el.className = 'w-full';
    } else {
        el = document.createElement('div');
        el.innerHTML = `<i class="bi bi-file-earmark-text text-6xl text-white"></i><p class="text-white mt-2">${file.name}</p>`;
        el.className = 'text-center';
    }

    content.appendChild(el);
    document.getElementById('modalViewPreview').classList.remove('hidden');
}


document.getElementById('fecharPreviewView').onclick = () => {
    document.getElementById('modalViewPreview').classList.add('hidden');
};

document.getElementById('prevPreview').onclick = () => {
    if (midiaPreviewIndex > 0) abrirVisualizacaoIndividual(midiaPreviewIndex - 1);
};

document.getElementById('nextPreview').onclick = () => {
    if (midiaPreviewIndex < arquivosSelecionados.length - 1) abrirVisualizacaoIndividual(midiaPreviewIndex + 1);
};


btnFecharModal.addEventListener('click', () => modal.classList.add('hidden'));

function showToast(mensagem, tipo = 'erro') {
    const toast = document.createElement('div');
    toast.className = `px-4 py-3 rounded shadow text-white text-sm transition-opacity duration-300 ${
        tipo === 'erro' ? 'bg-red-600' : 'bg-green-600'
    }`;

    toast.innerText = mensagem;

    const container = document.getElementById('toastContainer');
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}


btnConfirmarEnvio.addEventListener('click', () => {
    if (!arquivosSelecionados.length) return;

    modal.classList.add('hidden');
    const mensagensDiv = document.getElementById('mensagens-chat');

    // Exibir no chat cada card com "enviando..."
    arquivosSelecionados.forEach(arquivo => {
        const tipo = arquivo.type.startsWith('image/') ? 'image'
                   : arquivo.type.startsWith('video/') ? 'video'
                   : arquivo.type.startsWith('audio/') ? 'audio'
                   : 'document';

        const url = URL.createObjectURL(arquivo);

        let mediaHTML = '';
        if (tipo === 'image') {
            mediaHTML = `<img src="${url}" class="max-w-[200px] max-h-[200px] rounded-lg">`;
        } else if (tipo === 'video') {
            mediaHTML = `<video src="${url}" controls class="max-w-[200px] rounded-lg"></video>`;
        } else if (tipo === 'audio') {
            mediaHTML = `<audio src="${url}" controls class="w-full"></audio>`;
        } else {
            mediaHTML = `<div class="flex items-center gap-2 text-gray-700 text-sm">
                            <i class="bi bi-file-earmark-text text-xl"></i> ${arquivo.name}
                         </div>`;
        }

        const card = `
            <div class="flex justify-end mb-2">
                <div class="bg-[#D9FDD3] rounded-2xl p-3 max-w-[80%] text-sm shadow">
                    ${mediaHTML}
                    <small class="block text-xs text-gray-500 mt-2">enviando...</small>
                </div>
            </div>
        `;

        mensagensDiv.insertAdjacentHTML('beforeend', card);
    });

    document.getElementById('chat-mensagens')?.scrollTo(0, mensagensDiv.scrollHeight);

    // Enviar um por um
    arquivosSelecionados.forEach(arquivo => {
        const tipo = arquivo.type.startsWith('image/') ? 'image'
                   : arquivo.type.startsWith('video/') ? 'video'
                   : arquivo.type.startsWith('audio/') ? 'audio'
                   : 'document';

        const formData = new FormData();
        formData.append('numero', numeroAtualSelecionado);
        formData.append('caption', legendaMidia.value);
        formData.append('mediatype', tipo);
        formData.append('arquivos[]', arquivo);

        fetch("/api/evolution/enviar-midia", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": window.CSRF_TOKEN },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            carregarNovasMensagens();

            if (data?.resultados) {
                data.resultados.forEach(res => {
                    const sucesso = res.status === 'enviado';

                    const toast = document.createElement('div');
                    toast.className = `px-4 py-2 rounded shadow text-sm flex items-center gap-2 animate-fadeInDown ${
                        sucesso ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                    }`;

                    toast.innerHTML = `
                        <i class="bi ${sucesso ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} text-lg"></i>
                        <div>
                            <strong>${res.arquivo}</strong><br>
                            ${sucesso ? 'Enviado com sucesso!' : `Erro: ${res.mensagem}`}
                        </div>
                    `;

                    document.getElementById('toastContainer').appendChild(toast);
                    setTimeout(() => toast.remove(), 4000);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao enviar mídia:', error);
            alert('Erro ao enviar uma das mídias. Verifique sua conexão e tente novamente.');
        });
    });

    arquivosSelecionados = [];
    document.getElementById('inputFotoVideo').value = '';
});

const emojiPickerContainer = document.getElementById('emojiPicker');

// Fechar ao clicar fora
document.addEventListener('click', (e) => {
    if (!emojiPickerContainer.contains(e.target) && e.target !== btnEmoji) {
        emojiPickerContainer.classList.add('hidden');
    }
});

function insertEmoji(emoji) {
    inputMensagemEmoji.focus();

    const range = document.createRange();
    range.selectNodeContents(inputMensagemEmoji);
    range.collapse(false);

    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);

    const emojiNode = document.createTextNode(emoji);
    range.insertNode(emojiNode);

    range.setStartAfter(emojiNode);
    sel.removeAllRanges();
    sel.addRange(range);
}



let mediaItens = [];
let mediaIndex = 0;

// Função para abrir o modal
function abrirModalMedia(index) {
    const modal = document.getElementById("modalViewMedia");
    const content = document.getElementById("contentViewMedia");

    const item = mediaItens[index];
    content.innerHTML = '';

    if (item.type === 'image') {
        const img = document.createElement('img');
        img.src = item.src;
        img.classList.add("max-h-[75vh]", "mx-auto", "rounded");
        content.appendChild(img);
    } else if (item.type === 'audio') {
        const audio = document.createElement('audio');
        audio.src = item.src;
        audio.controls = true;
        audio.classList.add("w-full", "rounded");
        content.appendChild(audio);

        // Tentar dar autoplay após adicionar no DOM
        setTimeout(() => audio.play().catch(() => {}), 100);
    } else if (item.type === 'video') {
        const video = document.createElement('video');
        video.src = item.src;
        video.controls = true;
        video.classList.add("w-full", "rounded", "bg-black");
        video.autoplay = true;
        content.appendChild(video);
    }

    modal.classList.remove('hidden');
}

// Ao clicar na mídia, abrir o modal
document.addEventListener('click', (e) => {
    if (e.target.closest('.media-clickable')) {
        const allMedia = Array.from(document.querySelectorAll('.media-clickable'));
        mediaItens = allMedia.map(el => ({
            type: el.dataset.type,
            src: el.dataset.src
        }));
        mediaIndex = allMedia.indexOf(e.target.closest('.media-clickable'));
        abrirModalMedia(mediaIndex);
    }
});

// Fechar o modal no botão fechar
document.getElementById('fecharViewMedia').addEventListener('click', () => {
    document.getElementById("modalViewMedia").classList.add('hidden');
});

// Fechar o modal clicando fora (overlay)
document.getElementById('modalViewMedia').addEventListener('click', (e) => {
    if (e.target.id === 'modalViewMedia') {
        document.getElementById("modalViewMedia").classList.add('hidden');
    }
});

// Botões de próxima e anterior
document.getElementById('prevMedia').addEventListener('click', () => {
    mediaIndex = (mediaIndex - 1 + mediaItens.length) % mediaItens.length;
    abrirModalMedia(mediaIndex);
});

document.getElementById('nextMedia').addEventListener('click', () => {
    mediaIndex = (mediaIndex + 1) % mediaItens.length;
    abrirModalMedia(mediaIndex);
});

// Teclado: ESC fecha e seta para os lados navega
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById("modalViewMedia");
    if (modal.classList.contains('hidden')) return;

    if (e.key === 'Escape') {
        modal.classList.add('hidden');
    } else if (e.key === 'ArrowRight') {
        document.getElementById('nextMedia').click();
    } else if (e.key === 'ArrowLeft') {
        document.getElementById('prevMedia').click();
    }
});

function criarPlayerAudio(url) {
    const container = document.createElement("div");
    container.className = "custom-audio-player flex items-center gap-4 bg-gray-100 rounded-lg p-3";

    const btn = document.createElement("button");
    btn.className = "play-pause text-orange-500 text-2xl";
    btn.innerHTML = '<i class="bi bi-play-fill"></i>';

    const barra = document.createElement("div");
    barra.className = "progress-bar flex-1 bg-gray-300 rounded h-2 cursor-pointer";
    const progresso = document.createElement("div");
    progresso.className = "progress bg-orange-500 h-2 rounded";
    progresso.style.width = "0%";
    barra.appendChild(progresso);

    const tempo = document.createElement("div");
    tempo.className = "time text-sm text-gray-600";
    tempo.textContent = "0:00 / 0:00";

    container.appendChild(btn);
    container.appendChild(barra);
    container.appendChild(tempo);

    const audio = new Audio(url);

    audio.addEventListener("loadedmetadata", () => {
        tempo.textContent = `0:00 / ${formatarTempo(Math.floor(audio.duration))}`;
    });

    audio.addEventListener("timeupdate", () => {
        const pct = (audio.currentTime / audio.duration) * 100;
        progresso.style.width = pct + "%";
        tempo.textContent = `${formatarTempo(Math.floor(audio.currentTime))} / ${formatarTempo(Math.floor(audio.duration))}`;
    });

    audio.addEventListener("ended", () => {
        btn.innerHTML = '<i class="bi bi-play-fill"></i>';
    });

    btn.addEventListener("click", () => {
        if (audio.paused) {
            document.querySelectorAll(".custom-audio-player-container").forEach(container => {
                const url = container.dataset.src;
                const player = criarPlayerAudio(url);
                container.appendChild(player);
            });

            audio.play();
            btn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        } else {
            audio.pause();
            btn.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
    });

    barra.addEventListener("click", (e) => {
        const pct = e.offsetX / barra.offsetWidth;
        audio.currentTime = pct * audio.duration;
    });

    return container;
}

function formatarTempo(segundos) {
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    return `${m}:${s < 10 ? '0' + s : s}`;
}

function filtrarContatos() {
    const termo = document.getElementById('pesquisa-contato').value.toLowerCase();
    const contatos = document.querySelectorAll('#lista-contatos-itens .contato');

    contatos.forEach(contato => {
        const numero = contato.querySelector('.numero-cliente').textContent.toLowerCase();

        if (numero.includes(termo)) {
            contato.style.display = 'flex'; // Mostrar se bater
        } else {
            contato.style.display = 'none'; // Esconder se não bater
        }
    });
}