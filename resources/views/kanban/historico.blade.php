@extends('layouts.dashboard')
<title>Zabulon {{ $numero }}</title>
@section('content')

<div class="p-6">
    <!-- Header superior -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
        <h2 class="text-2xl font-bold text-[#FE7F32] flex items-center gap-2">
            <div class="flex items-center gap-3 mb-4">
                <img src="{{ $fotoPerfil ?? asset('img/user-default.png') }}"
                alt="Foto de perfil"
                onerror="this.onerror=null;this.src='{{ asset('img/default/user.png') }}';"
                class="w-12 h-12 rounded-full border shadow object-cover">
                <div>
                    <h2 class="text-lg font-semibold">Conversa com {{ $numero }}</h2>
                </div>
            </div>
        </h2>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow flex items-center gap-2">
                <i class="bi bi-power"></i> Desligar Bot
            </button>
            <a href="{{ route('kanban.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg shadow flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Chat container -->
    <div class="flex flex-col bg-white rounded-xl shadow border max-h-[70vh] h-[70vh]">
        <!-- Mensagens -->
        <div id="historico" class="flex-1 overflow-y-auto p-4 space-y-3">
            <!-- Conteúdo preenchido via AJAX -->
        </div>

        <!-- Enviar mensagem -->
        <div class="flex items-center gap-2 mt-4 border-t px-4 py-4 bg-gray-50">
            <input 
                type="text" 
                id="mensagem-texto" 
                placeholder="Digite sua mensagem..." 
                class="flex-1 p-3 border rounded shadow focus:outline-none focus:ring-2 focus:ring-[#FE7F32]"
                onkeydown="if(event.key === 'Enter') enviarMensagem()"
            >
            <button 
                onclick="enviarMensagem()" 
                class="bg-[#FE7F32] text-white px-4 py-2 rounded shadow hover:bg-orange-700 flex items-center gap-2"
            >
                <i class="bi bi-send-fill"></i> Enviar
            </button>
        </div>
    </div>
</div>

<!-- Lógica original mantida -->
<script>
    let ultimaMensagemId = null;

    function enviarMensagem() {
        const numero = "{{ $numero }}";
        const mensagem = document.getElementById('mensagem-texto').value;

        if (!mensagem.trim()) return;

        fetch('{{ route('kanban.enviar-mensagem') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ numero, mensagem })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'Mensagem enviada com sucesso') {
                document.getElementById('mensagem-texto').value = '';
                carregarMensagens(true); // força o scroll após enviar
            } else {
                alert(data.erro || 'Erro ao enviar.');
            }
        });
    }

    function carregarMensagens(forcarScroll = false) {
        const container = document.getElementById('historico');
        const estavaNoFinal = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;

        fetch(`/kanban/historico/{{ $numero }}/atualizar`)
            .then(res => res.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;

                const novasMsgs = temp.querySelectorAll('[data-id]');
                if (novasMsgs.length === 0) return;

                const novaUltimaId = novasMsgs[novasMsgs.length - 1].getAttribute('data-id');

                if (novaUltimaId === ultimaMensagemId && !forcarScroll) return;

                ultimaMensagemId = novaUltimaId;
                container.innerHTML = html;

                if (estavaNoFinal || forcarScroll) {
                    container.scrollTop = container.scrollHeight;
                }
            });
    }

    carregarMensagens();
    setInterval(carregarMensagens, 1500);
    
window.togglePlay = function (btn) {
    const container = btn.closest('div');
    const audio = container.querySelector('.audio-player');
    const progressBar = container.querySelector('.progress-bar');
    const timeDisplay = container.querySelector('span');
    const icon = btn.querySelector('i');

    // Pausar outros áudios
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

    // Progresso e tempo
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
