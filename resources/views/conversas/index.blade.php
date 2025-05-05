@extends('layouts.dashboard')
<title>Zabulon - Conversas</title>

@section('content')
    <style>
        @keyframes barraAndando {
            0% {
                left: -33%;
            }

            100% {
                left: 100%;
            }
        }

        .animate-barra {
            animation: barraAndando 1.5s linear infinite;
        }

        #gravandoContainer button {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
        }

        #menuAnexar {
            transition: all 0.2s ease;
        }

        #gravandoContainer .barra-gravacao {
            flex-grow: 1;
            height: 4px;
            background-color: #ddd;
            border-radius: 9999px;
            position: relative;
            overflow: hidden;
        }

        /* Botão + (normal e ativo) */
        #btnAdicionar {
            transition: transform 0.2s ease, color 0.2s ease;
        }

        #btnAdicionar.open {
            transform: rotate(45deg);
            color: #fb923c;
            /* Laranja Zabulon quando ativo */
        }

        #barraAnimada {
            position: absolute;
            left: 0;
            top: 0;
            width: 25%;
            height: 100%;
            background-color: #fb923c;
        }

        #tempoGravado {
            width: 40px;
            text-align: center;
            font-size: 14px;
        }

        #input-mensagem {
            outline: none;
            word-break: break-word;
            overflow-wrap: break-word;
            font-family: 'Apple Color Emoji', 'Segoe UI Emoji', 'Noto Color Emoji', sans-serif;
            font-size: 16px;
        }

        #input-mensagem:empty:before {
            content: attr(placeholder);
            color: #aaa;
        }

        #input-mensagem::-webkit-scrollbar {
            width: 6px;
        }

        #input-mensagem::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 9999px;
        }

        #input-mensagem::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .barra-input {
            border-width: 2px;
            border-color: #ddd;
            transition: border-radius 0.2s ease, border-width 0.2s ease;
        }

        .barra-input.scrolling {
            border-width: 1px;
            border-radius: 0.75rem;
            /* reduz a borda */
        }
    </style>

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

            <div id="area-input" class="flex items-center gap-3 p-4 border-t bg-white hidden">

                <!-- Caixa de Mensagem com ícones dentro -->
                <div class="flex items-end border rounded-full px-4 py-2 bg-gray-100 flex-1 space-x-3 barra-input relative">

                    <!-- Botão Emoji (padronizado com o resto) -->
                    <button id="btnEmoji" class="text-gray-500 hover:text-gray-700">
                        <i class="bi bi-emoji-smile text-xl"></i>
                    </button>

                    <!-- Picker container -->
                    <div id="emojiPicker" class="hidden" style="position:absolute; bottom:60px; left:20px; z-index:999;">
                    </div>




                    <!-- Botão + com menu dentro -->
                    <div class="relative">
                        <button id="btnAdicionar" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-plus-lg text-xl"></i>
                        </button>

                        <div id="menuAnexar"
                            class="absolute bottom-12 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-xl border border-gray-200 w-44 hidden z-50">
                            <ul class="divide-y divide-gray-200">
                                <li>
                                    <button id="btnAnexarFotoVideo"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition text-sm text-gray-700 w-full text-left">
                                        <i class="bi bi-file-earmark-image text-blue-500 text-lg"></i> Foto / Vídeo
                                    </button>
                                </li>
                                <li>
                                    <button id="btnAnexarAudio"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition text-sm text-gray-700 w-full text-left">
                                        <i class="bi bi-mic-fill text-green-500 text-lg"></i> Áudio
                                    </button>
                                </li>
                                <li>
                                    <button id="btnAnexarDocumento"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition text-sm text-gray-700 w-full text-left">
                                        <i class="bi bi-file-earmark-text text-purple-500 text-lg"></i> Documento
                                    </button>
                                </li>
                            </ul>
                            <input type="file" id="inputFotoVideo" accept="image/*,video/*" style="display:none;">
                            <input type="file" id="inputAudio" accept="audio/*" style="display:none;">
                            <input type="file" id="inputDocumento" accept=".pdf,.doc,.docx,.txt,.xlsx,.xls,.zip"
                                style="display:none;">
                        </div>

                    </div>

                    <!-- Área de digitação -->
                    <div class="flex-1 max-h-28 overflow-y-auto" id="input-mensagem" contenteditable="true"
                        placeholder="Digite uma mensagem..."
                        onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); enviarMensagem() }">
                    </div>

                    <!-- Botão iniciar gravação -->
                    <button id="btnIniciarGravacao" class="text-orange-500 hover:text-orange-600">
                        <i class="bi bi-mic-fill text-xl"></i>
                    </button>

                    <!-- Botão enviar texto -->
                    <button class="text-orange-500 hover:text-orange-600" onclick="enviarMensagem()">
                        <i class="bi bi-send-fill text-xl"></i>
                    </button>

                </div>


                <!-- Área de gravação (ativa quando gravando) -->
                <div id="gravandoContainer"
                    class="hidden flex items-center gap-3 flex-1 ml-2 bg-gray-100 rounded-full px-3 py-2 shadow-sm">
                    <button id="btnCancelarAudio" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-md">
                        <i class="bi bi-trash-fill"></i>
                    </button>

                    <button id="btnPausarContinuarAudio"
                        class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-full shadow-md">
                        <i class="bi bi-pause-fill"></i>
                    </button>

                    <button id="btnEnviarAudio"
                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-full shadow-md">
                        <i class="bi bi-send-fill"></i>
                    </button>

                    <div class="barra-gravacao relative w-40 h-2 rounded-full overflow-hidden bg-gray-200">
                        <div id="barraAnimada" class="absolute left-0 top-0 h-full w-1/3 bg-orange-500 animate-barra"></div>
                    </div>

                    <div id="tempoGravado" class="text-sm w-10 text-center">0:00</div>
                </div>

            </div>





        </div>
    </div>


<!-- Modal de Preview -->
<div id="modalPreview" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-4 space-y-4 relative">

        <!-- Botão fechar -->
        <button id="fecharModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 class="text-lg font-bold text-gray-800">Pré-visualizar envio</h2>

        <!-- Preview com spinner -->
        <div id="previewMidiaContainer" class="relative flex justify-center items-center bg-gray-100 p-4 rounded-lg max-h-60 overflow-auto">
            <div id="spinnerPreview" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <div id="previewMidia" class="w-full flex justify-center items-center"></div>
        </div>

        <input type="text" id="legendaMidia" placeholder="Escreva uma legenda (opcional)" class="w-full border rounded px-3 py-2 text-sm">

        <button id="confirmarEnvio" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded w-full">Enviar</button>
    </div>
</div>



    <script>
        window.ROTA_ENVIAR_MENSAGEM = "{{ route('kanban.enviar-mensagem') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
        window.WEBSOCKET_TOKEN = "{{ env('WEBSOCKET_TOKEN') }}";
    </script>
    <script src="{{ asset('js/conversar/conversar.js') }}"></script>
@endsection
