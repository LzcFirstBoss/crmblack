@extends('layouts.dashboard')
@section('content')

<div class="p-6">
    <!-- Header superior -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-orange-600">
            <i class="bi bi-chat-dots-fill"></i> Conversa com {{ $numero }}
        </h2>
        <div class="space-x-2">
            <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                <i class="bi bi-power"></i> Desligar Bot
            </button>
            <a href="{{ route('kanban.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                ← Voltar
            </a>
        </div>
    </div>

    <!-- Chat container -->
    <div class="flex flex-col bg-white rounded shadow border max-h-[70vh] h-[70vh]">
        <!-- Mensagens -->
        <div id="historico" class="flex-1 overflow-y-auto p-4 space-y-3">
            <!-- Conteúdo preenchido via AJAX -->
        </div>

            <!--enviar msh -->

        <div class="flex items-center gap-2 mt-4 border-t pt-4">
            <input 
                type="text" 
                id="mensagem-texto" 
                placeholder="Digite sua mensagem..." 
                class="flex-1 p-3 border rounded shadow focus:outline-none focus:ring-2 focus:ring-orange-500"
                onkeydown="if(event.key === 'Enter') enviarMensagem()"
            >
            <button 
                onclick="enviarMensagem()" 
                class="bg-orange-600 text-white px-4 py-2 rounded shadow hover:bg-orange-700 flex items-center gap-2"
            >
                <i class="bi bi-send-fill"></i> Enviar
            </button>
        </div>
        
    </div>
</div>

<script>
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
                carregarMensagens(); // atualiza o chat
            } else {
                alert(data.erro || 'Erro ao enviar.');
            }
        });
    }

</script>

<script>
    function carregarMensagens() {
        const container = document.getElementById('historico');

        // Verifica se o scroll já está no final
        const estavaNoFinal = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;

        fetch(`/kanban/historico/{{ $numero }}/atualizar`)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;

                if (estavaNoFinal) {
                    container.scrollTop = container.scrollHeight;
                }
            });
    }


    // Carrega e atualiza a cada 3 segundos
    carregarMensagens();
    setInterval(carregarMensagens, 3000);
</script>

@endsection
