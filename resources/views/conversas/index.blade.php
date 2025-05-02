@extends('layouts.dashboard')
<title>Zabulon - Conversas</title>

@section('content')
    <div class="flex h-[calc(100vh-70px)] bg-gray-100">
        <!-- Sidebar: Lista de Contatos -->
        <div class="w-1/3 border-r bg-white flex flex-col" id="lista-contatos">

            <!-- Header: Título + Pesquisa -->
            <div class="p-4 border-b bg-gray-50">
                <h2 class="text-lg font-bold text-gray-700 mb-3">Conversas</h2>

                <div class="relative">
                    <input type="text" id="pesquisa-contato" onkeyup="filtrarContatos()" placeholder="Buscar número..."
                        class="w-full pl-10 pr-4 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
                    <i class="bi bi-search absolute top-1/2 left-3 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Lista de Contatos -->
            <div class="flex-1 overflow-y-auto" id="lista-contatos-itens">
                @foreach ($contatos as $contato)
                    <div id="contato-{{ $contato->numero_cliente }}"
                        onclick="abrirConversa('{{ $contato->numero_cliente }}')"
                        class="contato flex items-center p-4 border-b cursor-pointer hover:bg-orange-100 transition">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-gray-800 numero-cliente">{{ $contato->numero_cliente }}</div>

                                @if ($contato->qtd_mensagens_novas > 0)
                                    <span
                                        class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-orange-500 rounded-full">
                                        {{ $contato->qtd_mensagens_novas }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ Str::limit($contato->mensagem_enviada, 40) }}
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 whitespace-nowrap ml-2">
                            {{ \Carbon\Carbon::parse($contato->data_e_hora_envio)->format('H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <!-- Área da Conversa -->
        <div class="w-2/3 flex flex-col bg-gray-50">
            <div class="flex items-center p-4 border-b bg-gray-100">
                <h2 id="titulo-contato" class="text-lg font-bold text-gray-700">Selecione uma conversa</h2>
            </div>

            <div id="chat-mensagens" class="flex-1 overflow-y-auto p-6 relative">
                <div id="mensagem-inicial"
                    class="flex flex-col items-center justify-center h-full text-center text-gray-400">
                    <i class="bi bi-chat-dots text-5xl text-orange-400 mb-4"></i>
                    <p class="text-lg font-semibold">Nenhuma conversa aberta</p>
                    <p class="text-sm text-gray-500">Selecione um contato ao lado para começar</p>
                </div>
                <div id="mensagens-chat" class="space-y-4 hidden"></div>
            </div>

            <!-- Input de enviar mensagem -->
            <div id="area-input" class="flex items-center gap-3 p-4 border-t bg-white hidden">
                <input type="text" id="input-mensagem" placeholder="Digite uma mensagem..."
                    class="flex-1 border rounded-lg px-3 py-2 text-sm">
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Enviar</button>
            </div>
        </div>


    </div>

    <script>
        let socket = null;
        let numeroAtualSelecionado = null;

        function conectarWebSocket() {
            const token = "{{ env('WEBSOCKET_TOKEN') }}";
            const socket = new WebSocket(`http://localhost:3000?token=${token}`);

            socket.onopen = () => {
                console.log('Conectado ao WebSocket');
            };

            socket.onmessage = (event) => {
                const mensagem = JSON.parse(event.data);

                if (mensagem.evento === 'novaMensagem') {
                    const dados = mensagem.dados;

                    if (dados.numero === numeroAtualSelecionado) {
                        carregarNovasMensagens();
                    }
                }
            };

            socket.onclose = () => {
                console.log('WebSocket desconectado');
                // Se quiser reconectar automaticamente depois, é aqui que implementa
            };

            socket.onerror = (error) => {
                console.error('Erro no WebSocket:', error);
            };
        }

        function abrirConversa(numero) {
            numeroAtualSelecionado = numero;

            // Atualizar o título
            document.getElementById('titulo-contato').innerText = numero;

            // Mostrar o input
            document.getElementById('area-input').classList.remove('hidden');

            // Esconder a tela inicial e mostrar o chat real
            document.getElementById('mensagem-inicial').classList.add('hidden');
            document.getElementById('mensagens-chat').classList.remove('hidden');

            // Limpar seleção anterior e destacar novo contato
            document.querySelectorAll('.contato').forEach(c => c.classList.remove('bg-gray-200'));
            const contatoSelecionado = document.getElementById('contato-' + numero);
            if (contatoSelecionado) {
                contatoSelecionado.classList.add('bg-gray-200');
            }

            document.getElementById('mensagens-chat').innerHTML = `
        <div class="w-full flex justify-center items-center py-10 text-gray-400">
            <i class="bi bi-arrow-repeat animate-spin text-2xl mr-2"></i> Carregando mensagens...
        </div>`;

            // Carregar as mensagens da conversa
            fetch('/conversar/' + numero)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('mensagens-chat').innerHTML = html;

                    setTimeout(() => {
                        const containerChat = document.getElementById('chat-mensagens');
                        containerChat.scrollTop = containerChat.scrollHeight;
                    }, 50);
                });


            // Zerar mensagens novas no banco
            fetch('/zerar-mensagens-novas/' + numero, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            }).then(() => {
                atualizarListaContatos();
            });
        }

        function carregarNovasMensagens() {
            fetch('/conversar/' + numeroAtualSelecionado)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('mensagens-chat').innerHTML = html;

                    setTimeout(() => {
                        const containerChat = document.getElementById('chat-mensagens');
                        containerChat.scrollTop = containerChat.scrollHeight;
                    }, 50);
                });
        }

        function atualizarListaContatos() {
            fetch('/conversar-parcial')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('lista-contatos-itens').innerHTML = html;

                    if (numeroAtualSelecionado) {
                        const contatoSelecionado = document.getElementById('contato-' + numeroAtualSelecionado);
                        if (contatoSelecionado) {
                            contatoSelecionado.classList.add('bg-gray-200');
                        }
                    }
                });
        }

        function filtrarContatos() {
            let input = document.getElementById('pesquisa-contato').value.toLowerCase();
            let contatos = document.querySelectorAll('#lista-contatos-itens .contato');

            contatos.forEach(function(contato) {
                let numero = contato.querySelector('.numero-cliente')?.innerText.toLowerCase() || '';

                if (numero.includes(input)) {
                    contato.style.display = '';
                } else {
                    contato.style.display = 'none';
                }
            });
        }

        // Atualizar lista de contatos a cada 2 segundos
        setInterval(atualizarListaContatos, 2000);

        // Conectar no WebSocket assim que abrir a página
        conectarWebSocket();
    </script>
@endsection
