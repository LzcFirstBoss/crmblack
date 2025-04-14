@extends('layouts.dashboard')
@section('content')

<div class="p-6">
    <!-- Header superior -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
        <h2 class="text-2xl font-bold text-[#FE7F32] flex items-center gap-2">
            <i class="bi bi-chat-dots-fill text-3xl"></i> Conversa com {{ $numero }}
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
</script>


@endsection
