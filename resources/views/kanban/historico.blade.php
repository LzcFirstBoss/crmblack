@extends('layouts.dashboard')
<title>Zabulon {{ $numero }}</title>
@section('content')

<div class="flex flex-col bg-white h-full w-full rounded-lg shadow-md">
    <!-- Header: Foto, Nome, Botões -->
    <div class="flex items-center justify-between p-4 border-b bg-gray-100">
        <div class="flex items-center gap-3">
            <img src="{{ $fotoPerfil ?? asset('img/user-default.png') }}" onerror="this.onerror=null;this.src='{{ asset('img/default/user.png') }}';" class="w-12 h-12 rounded-full border shadow object-cover">
            <div class="leading-tight">
                <h2 class="text-lg font-bold text-gray-800">Conversa com {{ $numero }}</h2>
            </div>
        </div>
        <div class="flex gap-2">
            @php
                $numeroLimpo = preg_replace('/[^0-9]/', '', $numero);
            @endphp
            <a href="{{ route('cliente.alternarBot', ['numero' => $numeroLimpo]) }}" class="flex items-center gap-2 px-4 py-2 rounded-full text-white {{ $cliente->botativo ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-sm">
                <i class="bi bi-power"></i> {{ $cliente->botativo ? 'Desligar Bot' : 'Ligar Bot' }}
            </a>
            <a href="{{ route('kanban.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-full bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Mensagens -->
    <div id="historico" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
        <!-- Mensagens carregadas dinamicamente -->
    </div>

    <!-- Área de digitação -->
    <div class="flex items-center gap-3 p-4 border-t bg-white">
        <input 
            type="text" 
            id="mensagem-texto" 
            placeholder="Digite uma mensagem..." 
            class="flex-1 p-3 rounded-full border shadow-sm focus:outline-none focus:ring-2 focus:ring-[#FE7F32] text-gray-700"
            onkeydown="if(event.key === 'Enter') enviarMensagem()"
        >
        <button 
            onclick="enviarMensagem()" 
            class="flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-[#FE7F32] hover:bg-orange-600 text-white shadow-md"
        >
            <i class="bi bi-send-fill text-lg"></i>
        </button>
    </div>
</div>

<style>
    .mensagem-recebida {
        background-color: #e5e7eb;
        color: #111827;
        align-self: flex-start;
        padding: 10px 15px;
        border-radius: 18px 18px 18px 4px;
        max-width: 75%;
        word-break: break-word;
    }
    .mensagem-enviada {
        background-color: #FE7F32;
        color: #ffffff;
        align-self: flex-end;
        padding: 10px 15px;
        border-radius: 18px 18px 4px 18px;
        max-width: 75%;
        word-break: break-word;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        carregarMensagens(true);
        setInterval(carregarMensagens, 1500);
    });

    let ultimaMensagemId = null;

    function limparNumero(numero) {
    numero = numero.replace(/\D/g, ''); // remove tudo que não for número
    return numero + '@s.whatsapp.net';
}

function enviarMensagem() {
    const numeroOriginal = "{{ $numero }}";
    const mensagemInput = document.getElementById('mensagem-texto');
    const mensagem = mensagemInput.value.trim();

    if (!mensagem) return;

    mensagemInput.value = '';
    mensagemInput.focus();

    const numero = limparNumero(numeroOriginal);

    fetch('{{ route('kanban.enviar-mensagem') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ numero, mensagem })
    })
    .then(res => res.json())
    .then(async data => {
        if (data.status === 'Mensagem enviada com sucesso') {
            await carregarMensagens(true);
        } else {
            alert(data.erro || 'Erro ao enviar a mensagem.');
        }
    })
    .catch(error => {
        console.error('Erro ao enviar:', error);
        alert('Erro de conexão. Tente novamente.');
    });
}




async function carregarMensagens(forcarScroll = false) {
    const container = document.getElementById('historico');
    const estavaNoFinal = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;

    try {
        const response = await fetch(`/kanban/historico/{{ $numero }}/atualizar`);
        const html = await response.text();

        const temp = document.createElement('div');
        temp.innerHTML = html;

        const novasMsgs = temp.querySelectorAll('[data-id]');
        if (novasMsgs.length === 0) return;

        const novaUltimaId = novasMsgs[novasMsgs.length - 1].getAttribute('data-id');

        if (novaUltimaId === ultimaMensagemId && !forcarScroll) return;

        ultimaMensagemId = novaUltimaId;
        container.innerHTML = html;

        // ✅ Agora, só depois de realmente atualizar o HTML, fazemos o scroll
        if (estavaNoFinal || forcarScroll) {
            setTimeout(() => {
                container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
            }, 50); // Pequeno delay para garantir que o DOM atualizou
        }
    } catch (error) {
        console.error('Erro ao carregar mensagens:', error);
    }
}

    window.togglePlay = function (btn) {
        const container = btn.closest('div');
        const audio = container.querySelector('.audio-player');
        const progressBar = container.querySelector('.progress-bar');
        const timeDisplay = container.querySelector('span');
        const icon = btn.querySelector('i');

        document.querySelectorAll('audio').forEach(a => {
            if (a !== audio) a.pause();
        });

        document.querySelectorAll('.play-pause-btn i').forEach(i => {
            if (i !== icon) i.className = 'bi bi-play-fill';
        });

        if (audio.paused) {
            audio.play();
            icon.className = 'bi bi-pause';
        } else {
            audio.pause();
            icon.className = 'bi bi-play-fill';
        }

        audio.ontimeupdate = () => {
            if (audio.duration && progressBar) {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressBar.style.width = percent + "%";
            }

            if (timeDisplay) {
                const minutos = Math.floor(audio.currentTime / 60);
                const segundos = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
                timeDisplay.textContent = `${minutos}:${segundos}`;
            }
        };

        audio.onended = () => {
            icon.className = 'bi bi-play-fill';
            if (progressBar) progressBar.style.width = "0%";
            if (timeDisplay) timeDisplay.textContent = "0:00";
        };
    }

    function abrirModalImagem(url) {
        const modal = document.createElement('div');
        modal.className = "fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50";
        modal.innerHTML = `
            <div class="relative max-w-[90%] max-h-[90%]">
                <img src="${url}" class="rounded-lg max-h-[90vh] max-w-[90vw] object-contain">
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="absolute top-2 right-2 bg-white text-black rounded-full w-8 h-8 flex items-center justify-center font-bold">
                    ✕
                </button>
            </div>`;
        document.body.appendChild(modal);
    }
</script>


@endsection
