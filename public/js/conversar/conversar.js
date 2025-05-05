let socket = null;
let numeroAtualSelecionado = null;
let mediaRecorder = null;
let audioChunks = [];
let gravando = false;
let pausado = false;
let cancelado = false;
let tempoGravado = 0;
let intervaloTempo = null;

// Conectar WebSocket
function conectarWebSocket() {
    const token = window.WEBSOCKET_TOKEN;
    socket = new WebSocket(`ws://localhost:3000?token=${token}`);

    socket.onopen = () => console.log('Conectado ao WebSocket');
    socket.onmessage = (event) => {
        const mensagem = JSON.parse(event.data);
        if (mensagem.evento === 'novaMensagem' && mensagem.dados.numero === numeroAtualSelecionado) {
            carregarNovasMensagens();
        }
    };
}

conectarWebSocket();

function abrirConversa(numero) {
    numeroAtualSelecionado = numero;

    document.getElementById('titulo-contato').innerText = numero;
    document.getElementById('area-input').classList.remove('hidden');
    document.getElementById('mensagem-inicial').classList.add('hidden');
    document.getElementById('mensagens-chat').classList.remove('hidden');

    document.querySelectorAll('.contato').forEach(c => c.classList.remove('bg-gray-200'));
    document.getElementById('contato-' + numero)?.classList.add('bg-gray-200');

    document.getElementById('mensagens-chat').innerHTML = '<div class="w-full flex justify-center items-center py-10 text-gray-400"><i class="bi bi-arrow-repeat animate-spin text-2xl mr-2"></i> Carregando mensagens...</div>';

    fetch('/conversar/' + numero)
        .then(res => res.text())
        .then(html => {
            document.getElementById('mensagens-chat').innerHTML = html;
            document.getElementById('chat-mensagens')?.scrollTo(0, document.getElementById('chat-mensagens').scrollHeight);
        });

    fetch('/zerar-mensagens-novas/' + numero, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN }
    }).then(atualizarListaContatos);
}

function carregarNovasMensagens() {
    fetch('/conversar/' + numeroAtualSelecionado)
        .then(res => res.text())
        .then(html => {
            document.getElementById('mensagens-chat').innerHTML = html;
            document.getElementById('chat-mensagens')?.scrollTo(0, document.getElementById('chat-mensagens').scrollHeight);
        });
}

function atualizarListaContatos() {
    fetch('/conversar-parcial')
        .then(res => res.text())
        .then(html => {
            document.getElementById('lista-contatos-itens').innerHTML = html;
            if (numeroAtualSelecionado) {
                document.getElementById('contato-' + numeroAtualSelecionado)?.classList.add('bg-gray-200');
            }
        });
}

setInterval(atualizarListaContatos, 2000);

function enviarMensagem() {
    const mensagemInput = document.getElementById('input-mensagem');
    const mensagem = mensagemInput.textContent.trim(); // PEGAR O TEXTO CERTO

    if (!mensagem) return;

    mensagemInput.textContent = ''; // LIMPAR O CAMPO
    mensagemInput.focus();

    fetch(window.ROTA_ENVIAR_MENSAGEM, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
        body: JSON.stringify({ numero: numeroAtualSelecionado, mensagem })
    }).then(res => res.json())
    .then(data => {
        if (data.status === 'Mensagem enviada com sucesso') carregarNovasMensagens();
        else alert(data.erro || 'Erro ao enviar a mensagem.');
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

document.addEventListener('click', (e) => {
    if (!inputMensagemDiv.contains(e.target)) {
        window.getSelection().removeAllRanges();
    }
});

atualizarBorda();


// Detectar digitação e mudança de conteúdo
inputMensagem.addEventListener('input', atualizarBorda);

// Detectar scroll (opcional, caso role manualmente também)
inputMensagem.addEventListener('scroll', atualizarBorda);

// Remover seleção indesejada quando clica fora do campo
document.addEventListener('click', (e) => {
    if (!inputMensagem.contains(e.target)) {
        window.getSelection().removeAllRanges();
    }
});

// Rodar ao carregar para verificar se já tem conteúdo
atualizarBorda();



