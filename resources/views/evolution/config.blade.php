@extends('layouts.dashboard')
<title>Zabulon - Config</title>

@section('content')
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
        x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="-translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-300 transition"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-full opacity-0"
        class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
        @if (session('success'))
            <div
                class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded shadow flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @elseif (session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded shadow flex items-center gap-2">
                <i class="bi bi-x-circle-fill text-xl"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Container Único com fundo -->
    <div class="max-w-7xl mx-auto p-6 bg-gray-50 rounded-xl border border-gray-200 shadow-inner space-y-10">
        <!-- Bloco de Conexão WhatsApp -->
        <div class="bg-white p-6 rounded-lg shadow border" id="status">
            @if ($instancia && $instancia->status_conexao === 'CONNECTED')
                <h3 class="text-2xl font-semibold text-green-500 mb-2 flex gap-2 items-center">
                    <i class="bi bi-whatsapp"></i> Whatsapp Conectado
                    <span class="flex w-3 h-3 bg-green-500 rounded-full"></span>
                </h3>
                <p class="text-gray-600 mb-4 text-sm">
                    Instância <strong>{{ $instancia->nome }}</strong> está conectada ao número:
                    <strong>{{ $instancia->telefone ?? 'n/d' }}</strong>
                </p>
                <button id="btnDesconectar"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded flex items-center justify-center gap-2 transition">
                    <i class="bi bi-power"></i> Desconectar Whatsapp
                </button>
            @else
                <h3 class="text-2xl font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    <i class="bi bi-whatsapp"></i> Conectar Whatsapp
                </h3>
                <p class="text-red-600 mb-4 text-sm">
                    <i class="bi bi-exclamation-diamond-fill"></i> Em caso de erro ao ler o QRCODE recarregue a página e
                    tente novamente!.
                </p>
                <p class="text-gray-600 mb-4 text-sm">
                    Clique no botão abaixo para gerar o QR Code e conectar
                    seu número.
                </p>

                <button onclick="gerarQrCode()"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 rounded flex items-center justify-center gap-2 transition mb-4">
                    <i class="bi bi-qr-code"></i> Gerar QR Code
                </button>

                <div id="qrcode_result"
                    class="mt-6 border-dashed border-2 border-gray-300 rounded-lg py-10 flex flex-col items-center text-gray-400 text-sm">
                    <i class="bi bi-qr-code text-3xl mb-2"></i>
                    QR Code será exibido aqui
                </div>
            @endif
        </div>

        <!-- Bloco do Gerenciador de Bots -->
        <div x-data="{ open: false }">
            <!-- Bloco: Gerenciador de Bots -->
            <div class="bg-white p-6 rounded-lg shadow border space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-robot text-blue-600"></i> Gerenciador de Agentes
                    </h2>
                    <button @click="open = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded flex items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Novo Agente
                    </button>
                </div>

                <!-- Cards dos Bots -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($bots as $bot)
                        <div x-data="{ removendo: false }" x-show="!removendo"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="bg-white border rounded-lg shadow p-5 flex flex-col justify-between hover:shadow-lg transition">
                            <div class="mb-2">
                                <h3
                                class="text-base font-bold flex items-start gap-2 break-words leading-tight {{ $bot->ativo ? 'text-green-500' : 'text-gray-800' }}">
                                <i class="bi bi-robot flex-shrink-0 mt-1"></i>
                                <span class="break-words max-w-full">{{ $bot->nome }}</span>
                            </h3>
                            </div>

                            <p class="text-sm text-gray-600">
                                {{ $bot->descricao }}
                            </p>

                            <div class="flex justify-between items-center mt-5">
                                <button type="button"
                                    @click="$store.editorBot.abrir({
                                    id: {{ $bot->id }},
                                    nome: @js($bot->nome),
                                    descricao: @js($bot->descricao),
                                    prompt: @js($bot->prompt),
                                    ativo: {{ $bot->ativo ? 'true' : 'false' }},
                                    funcoes: @js($bot->funcoes ?? [])
                                })"
                                    class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>

                                @if (!$bot->ativo)
                                    <form action="{{ route('bots.destroy', $bot->id) }}" method="POST"
                                        @submit.prevent="
                                        removendo = true;
                                        setTimeout(() => $el.submit(), 300);
                                    ">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                                            <i class="bi bi-trash-fill"></i> Deletar
                                        </button>
                                    </form>
                                @else
                                    <div class="text-xs text-red-400">
                                        <i class="bi bi-lock-fill"></i> Agente ativo
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Modal de Criação -->
            <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div @click.away="open = false" class="bg-white w-full max-w-2xl p-6 rounded-lg shadow space-y-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="bi bi-robot text-blue-600"></i> Criar Novo Agente
                        </h2>
                        <button @click="open = false" class="text-gray-500 hover:text-red-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form x-data="{ loading: false }" @submit="loading = true" action="{{ route('bots.store') }}"
                        method="POST">
                        @csrf
                        <!-- Ativo -->
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" id="ativo" name="ativo" class="h-4 w-4">
                            <label for="ativo" class="text-sm text-gray-700">Agente Ativo</label>
                        </div>

                        <!-- Nome do Bot -->
                        <div>
                            <label for="nome" class="text-sm font-medium text-gray-700">Nome do Agente</label>
                            <input type="text" id="nome" name="nome"
                                class="w-full mt-1 border rounded p-2 text-sm" required>
                        </div>
                        <!-- Prompt base -->
                        <div>
                            <label for="prompt" class="text-sm font-medium text-gray-700">Prompt Base</label>
                            <textarea id="prompt" name="prompt" rows="6"
                                class="w-full mt-1 border rounded p-2 text-sm resize-y min-h-[150px] max-h-[400px]"
                                placeholder="Escreva o prompt base do bot..." required></textarea>
                        </div>


                        <div>
                            <label for="descricao" class="text-sm font-medium text-gray-700">Descrição breve <span
                                    class="text-gray-400">(máx. 300 caracteres)</span></label>
                            <textarea id="descricao" name="descricao" rows="6" maxlength="300"
                                class="w-full mt-1 border rounded p-2 text-sm resize-y min-h-[80px] max-h-[90px]"
                                placeholder="Escreva em resumo oque seu bot faz" required></textarea>
                        </div>

                        <!-- Lista de Funções com Switches -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Funções Disponíveis</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-60 overflow-y-auto border rounded p-4">
                                @foreach ($funcoes as $index => $funcao)
                                    <div x-data="{ ativado: false }"
                                        class="flex items-start justify-between gap-3 border-b pb-3">
                                        <div class="text-sm">
                                            <strong>{{ $funcao->nome }}</strong><br>
                                            <span class="text-gray-500 text-xs">{{ $funcao->descricao }}</span>
                                        </div>

                                        <label class="inline-flex items-center cursor-pointer">
                                            <!-- input hidden para envio real -->
                                            <input type="checkbox" name="funcoes[]" value="{{ $funcao->id }}"
                                                x-model="ativado" class="hidden">

                                            <!-- Switch visual -->
                                            <div class="w-11 h-6 bg-gray-300 rounded-full relative transition-colors duration-300"
                                                :class="{ 'bg-green-500': ativado }">

                                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
                                                    :class="{ 'translate-x-5': ativado }"></div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Botão de salvar -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" :disabled="loading"
                                class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-4 py-2 rounded text-sm flex items-center gap-2">
                                <template x-if="!loading">
                                    <span><i class="bi bi-save"></i> Salvar Agente</span>
                                </template>
                                <template x-if="loading">
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-arrow-repeat animate-spin"></i> Salvando...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <script>
            let statusInterval = null;

            function gerarQrCode() {
                const box = document.getElementById('qrcode_result');
                box.innerHTML = 'Carregando QR Code...';

                fetch(`/evolution/conectar`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.base64) {
                            box.innerHTML =
                                `<img src=\"${data.base64}\" class=\"w-64 h-64 object-contain rounded mx-auto\">`;
                            iniciarVerificacaoStatus();
                        } else {
                            box.innerHTML = `<p class=\"text-red-500\">Erro ao gerar QR Code.</p>`;
                        }
                    });
            }

            function iniciarVerificacaoStatus() {
                statusInterval = setInterval(() => {
                    fetch('/evolution/status')
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'CONNECTED') {
                                clearInterval(statusInterval);

                                const box = document.getElementById('status');
                                box.innerHTML = `
        <div class="flex flex-col items-center justify-center text-green-600 text-center space-y-4">
            <div id="lottie-check" class="w-32 h-32"></div>
            <p class="font-semibold text-lg"><i class="bi bi-whatsapp"></i> Whatsapp conectada com sucesso!</p>
        </div>
    `;

                                // Roda animação Lottie
                                lottie.loadAnimation({
                                    container: document.getElementById('lottie-check'),
                                    renderer: 'svg',
                                    loop: false,
                                    autoplay: true,
                                    path: 'https://assets2.lottiefiles.com/packages/lf20_jbrw3hcz.json' // animação de check verde
                                });

                                setTimeout(() => location.reload(), 2500); // recarrega depois da animação
                            }
                        });
                }, 3000);
            }

            document.getElementById('btnDesconectar').addEventListener('click', () => {
                if (!confirm('Tem certeza que deseja desconectar a instância?')) return;

                const btn = document.getElementById('btnDesconectar');
                const output = document.getElementById('status');
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                btn.disabled = true;
                output.classList.remove('hidden');
                output.innerHTML = `
            <div class="flex items-center justify-center gap-2 text-gray-600 text-sm animate-pulse">
                <i class="bi bi-arrow-repeat animate-spin"></i> Desconectando...
            </div>
        `;

                fetch('/evolution/logout', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'SUCCESS') {
                            output.innerHTML = `
                    <div class="flex items-center gap-2 text-green-600 font-medium">
                        <i class="bi bi-check-circle-fill text-xl"></i>
                        Instância desconectada com sucesso!
                    </div>
                `;
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            output.innerHTML = `
                    <div class="flex items-center gap-2 text-red-600 font-medium">
                        <i class="bi bi-x-circle-fill text-xl"></i>
                        Erro: ${data.response?.message || 'Não foi possível desconectar.'}
                    </div>
                `;
                        }
                    })
                    .catch(err => {
                        output.innerHTML = `
                <div class="text-red-600 font-medium">
                     Erro inesperado:
                    <pre class="bg-white border mt-2 p-2 rounded text-xs text-gray-800">${err}</pre>
                </div>
            `;
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
            });
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('editorBot', {
                    open: false,
                    botId: null,
                    botNome: '',
                    botDescricao: '',
                    botPrompt: '',
                    botAtivo: false,
                    botFuncoes: [],

                    abrir(bot) {
                        this.botId = bot.id;
                        this.botNome = bot.nome;
                        this.botDescricao = bot.descricao;
                        this.botPrompt = bot.prompt;
                        this.botAtivo = bot.ativo;
                        this.botFuncoes = bot.funcoes || [];
                        this.open = true;
                    }
                });
            });
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endsection
    <div x-data x-show="$store.editorBot.open" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div @click.away="$store.editorBot.open = false"
            class="bg-white w-full max-w-2xl p-6 rounded-lg shadow space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="bi bi-robot text-blue-600"></i> Editar Agente
                </h2>
                <button @click="$store.editorBot.open = false" class="text-gray-500 hover:text-red-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form x-data="{ loading: false }" @submit="loading = true" :action="`/bots/update/${$store.editorBot.botId}`"
                method="POST">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700">Agente Ativo</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" class="sr-only" x-model="$store.editorBot.botAtivo">
                        <div class="w-11 h-6 bg-gray-300 rounded-full relative transition-colors duration-300"
                            :class="{ 'bg-green-500': $store.editorBot.botAtivo }">
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
                                :class="{ 'translate-x-5': $store.editorBot.botAtivo }"></div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="text-sm font-medium">Nome</label>
                    <input type="text" name="nome" x-model="$store.editorBot.botNome"
                        class="w-full mt-1 border rounded p-2 text-sm" required>
                </div>

                <div>
                    <label class="text-sm font-medium">Descrição</label>
                    <textarea name="descricao" maxlength="300" x-model="$store.editorBot.botDescricao"
                        class="w-full mt-1 border rounded p-2 text-sm resize-y min-h-[80px] max-h-[100px]" required></textarea>
                </div>

                <div>
                    <label class="text-sm font-medium">Prompt</label>
                    <textarea name="prompt" x-model="$store.editorBot.botPrompt"
                        class="w-full mt-1 border rounded p-2 text-sm resize-y min-h-[150px] max-h-[400px]" required></textarea>
                </div>

                <div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Funções Disponíveis</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-60 overflow-y-auto border rounded p-4">
                            @foreach ($funcoes as $funcao)
                                @php $id = (string) $funcao->id; @endphp
                                <div class="flex items-start justify-between gap-3 border-b pb-3">
                                    <div class="text-sm">
                                        <strong>{{ $funcao->nome }}</strong><br>
                                        <span class="text-gray-500 text-xs">{{ $funcao->descricao }}</span>
                                    </div>

                                    <!-- Switch funcional com x-model no array -->
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only" :value="'{{ $id }}'"
                                            x-model="$store.editorBot.botFuncoes" name="funcoes[]">

                                        <!-- Toggle visual -->
                                        <div class="w-11 h-6 bg-gray-300 rounded-full relative transition-colors duration-300"
                                            :class="{
                                                'bg-green-500': $store.editorBot.botFuncoes.includes(
                                                    '{{ $id }}')
                                            }">
                                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
                                                :class="{
                                                    'translate-x-5': $store.editorBot.botFuncoes.includes(
                                                        '{{ $id }}')
                                                }">
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                    </div>

                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" :disabled="loading"
                        class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-4 py-2 rounded text-sm flex items-center gap-2">
                        <template x-if="!loading">
                            <span><i class="bi bi-save"></i> Salvar Alterações</span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <i class="bi bi-arrow-repeat animate-spin"></i> Salvando...
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>
