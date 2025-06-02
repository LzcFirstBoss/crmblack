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
        
        .destacar-msg {
            filter: brightness(0.85);
            transition: filter 0.3s ease;
        }

        #lista-contatos-itens::-webkit-scrollbar {
            width: 4px;
        }

        #lista-contatos-itens::-webkit-scrollbar-thumb {
            background-color: #fe803269;
            border-radius: 6px;
        }

        #lista-contatos-itens::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }
    </style>

    <div class="flex h-[calc(100vh-70px)] bg-gray-100">
        <!-- Sidebar: Lista de Contatos -->
        <div class="w-1/3 border-r bg-white flex flex-col" id="lista-contatos">

<!-- Header: Título + Pesquisa + Ações -->
<div class="p-4 border-b bg-gray-50">
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-bold text-gray-700">Conversas</h2>

        <div class="flex gap-2">
            <!-- Botão Filtro com Dropdown -->
            <div class="relative">
                <button onclick="toggleDropdownFiltro()" id="botaoFiltro" class="flex items-center gap-1 px-3 py-1.5 text-sm bg-white border rounded-lg text-gray-700 hover:bg-gray-100 transition">
    <i class="bi bi-funnel"></i>
    <span id="textoFiltro">Todas</span>
</button>

                <!-- Dropdown -->
                <div id="dropdownFiltro" class="absolute right-0 mt-2 w-40 bg-white border rounded-lg shadow-lg z-10 hidden">
                    <button onclick="filtrarContatosPorStatus('todas'); toggleDropdownFiltro()" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Todas</button>
                    <button onclick="filtrarContatosPorStatus('nao_lidas'); toggleDropdownFiltro()" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Não lidas</button>
                    <button onclick="filtrarContatosPorStatus('lidas'); toggleDropdownFiltro()" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Lidas</button>
                </div>
            </div>
            <!-- Botão Novo Lead -->
            <a href="{{ route('leads.create') }}"
                class="flex items-center gap-1 px-3 py-1.5 text-sm bg-orange-400 text-white rounded-lg hover:bg-orange-500 transition">
                    <i class="bi bi-person-plus-fill"></i>
                    Novo Lead
            </a>
        </div>
    </div>

    <div class="relative">
        <input type="text" id="pesquisa-contato" onkeyup="filtrarContatos()" placeholder="Buscar número..."
            class="w-full pl-10 pr-4 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400">
        <i class="bi bi-search absolute top-1/2 left-3 transform -translate-y-1/2 text-gray-400"></i>
    </div>
</div>


            <!-- Lista de Contatos -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden " id="lista-contatos-itens">
               
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
                <div id="mensagens-chat" class="space-y-4 hidden">
                    
                </div>
            </div>

            <!-- Bloco de resposta ativa -->
            <div id="respostaAtiva" class="hidden px-6 py-2 bg-white border-t border-b border-gray-200">
                <div class="flex items-center justify-between bg-gray-100 rounded-lg px-3 py-2 shadow-sm">
                    <div>
                        <div class="text-xs font-bold text-orange-500" id="respostaRemetente">Remetente</div>
                        <div class="text-sm text-gray-700 truncate" id="respostaTexto">Texto da mensagem original</div>
                    </div>
                    <button onclick="cancelarResposta()" class="text-gray-400 hover:text-red-500 text-sm ml-4">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
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
                            <input type="file" id="inputFotoVideo" accept="image/*,video/*" multiple style="display:none;">
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
<div id="modalPreview" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full p-6 space-y-5 relative">

        <!-- Botão fechar -->
        <button id="fecharModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-800">Pré-visualizar envio</h2>

        <!-- Preview com spinner -->
        <div id="previewMidiaContainer" class="relative bg-gray-100 p-4 rounded-lg max-h-[300px] overflow-x-auto">
            <div id="spinnerPreview" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <div id="previewMidia" class="flex overflow-x-auto space-x-4 p-2"></div>
        </div>

        <input type="text" id="legendaMidia" placeholder="Escreva uma legenda (opcional)" class="w-full border rounded px-3 py-2 text-sm">

        <button id="confirmarEnvio" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded w-full">Enviar</button>
    </div>
</div>

<div id="modalViewPreview" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 flex items-center justify-center px-4">
    <button id="fecharPreviewView" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
    <button id="prevPreview" class="absolute left-4 text-white text-2xl">&larr;</button>
    <button id="nextPreview" class="absolute right-4 text-white text-2xl">&rarr;</button>
    <div id="previewContent" class="max-w-3xl w-full flex justify-center items-center"></div>
</div>


<div id="modalViewMedia" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-4 relative space-y-4">
        <button id="fecharViewMedia" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        <div id="contentViewMedia" class="text-center"></div>

        <div class="flex justify-between mt-4">
            <button id="prevMedia" class="text-gray-400 hover:text-gray-600 text-2xl">&larr;</button>
            <button id="nextMedia" class="text-gray-400 hover:text-gray-600 text-2xl">&rarr;</button>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div id="modalEditarMensagem" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-[95%] max-w-xl p-6 relative animate-fade-in">
        <!-- Cabeçalho -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Editar mensagem</h2>
            <button onclick="fecharModalEditar()" class="text-gray-500 hover:text-black text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Área de edição com botão embutido -->
        <div class="flex items-center gap-2">
            <div class="relative flex-grow">
                <textarea id="inputEditarMensagem"
                          class="w-full border border-gray-300 rounded-lg pr-10 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                          rows="2"
                          placeholder="Digite sua nova mensagem..."></textarea>

                <!-- Botão salvar embutido à direita dentro do textarea -->
                <button onclick="confirmarEditarMensagem()"
                        class="absolute top-2 right-2 text-blue-600 hover:text-blue-800 transition"
                        title="Salvar">
                    <i class="bi bi-send-fill text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Campo oculto -->
        <input type="hidden" id="idMensagemEditar">
    </div>
</div>
    <script>
        window.ROTA_ENVIAR_MENSAGEM = "{{ route('kanban.enviar-mensagem') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
        window.WEBSOCKET_TOKEN = "{{ env('WEBSOCKET_TOKEN') }}";
        window.ROTA_APAGAR_MENSAGEM = "{{ route('mensagem.apagar') }}";
        window.numeroAtualSelecionado = "{{ $numero ?? '' }}";
    </script>
    <script src="{{ asset('js/conversar/conversar.js') }}"></script>
    <script src="{{ asset('js/conversar/apagar_editar.js') }}"></script>
    <script>
let filtroAtual = 'todas';
let dropdownAberto = false;

function toggleDropdownFiltro() {
    const dropdown = document.getElementById('dropdownFiltro');
    dropdownAberto = !dropdownAberto;

    if (dropdownAberto) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}


// Função única que aplica ambos os filtros
function aplicarFiltros() {
    const termo = document.getElementById('pesquisa-contato').value.toLowerCase();
    const contatos = document.querySelectorAll('#lista-contatos-itens .contato');

    contatos.forEach(contato => {
        const numero = contato.querySelector('.numero-cliente')?.textContent.toLowerCase() || '';
        const lido = contato.getAttribute('data-lido'); // 'sim' ou 'nao'

        let passaFiltroTexto = numero.includes(termo);
        let passaFiltroStatus = (
            filtroAtual === 'todas' ||
            (filtroAtual === 'nao_lidas' && lido === 'nao') ||
            (filtroAtual === 'lidas' && lido === 'sim')
        );

        contato.style.display = (passaFiltroTexto && passaFiltroStatus) ? 'flex' : 'none';
    });
}

// Aplicar filtro de texto ao digitar
function filtrarContatos() {
    aplicarFiltros();
}

// Alterar status do filtro e aplicar
function filtrarContatosPorStatus(status) {
    filtroAtual = status;

    // Atualiza o texto do botão com o filtro atual
    const textoFiltro = document.getElementById('textoFiltro');
    if (textoFiltro) {
        if (status === 'todas') textoFiltro.textContent = 'Todas';
        else if (status === 'nao_lidas') textoFiltro.textContent = 'Não lidas';
        else if (status === 'lidas') textoFiltro.textContent = 'Lidas';
    }

    aplicarFiltros();
}


    </script>
    <div id="toastContainer" class="fixed bottom-4 right-4 space-y-2 z-[9999]"></div>
@endsection
