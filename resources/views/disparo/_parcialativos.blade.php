@if ($disparos->isEmpty())
    <div class="max-w-6xl mx-auto text-center text-sm text-gray-500 mt-10">
        Nenhum disparo encontrado.
    </div>
@else
    <div class="max-w-6xl mx-auto mt-10">
        {{-- Container com scroll se tiver muitos --}}
        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow p-6 mt-8 max-h-[360px] overflow-y-auto">
            <h2 class="text-lg font-bold text-orange-800 mb-4 flex items-center gap-2">
                <i class="bi bi-broadcast-pin"></i> Todos os Disparos
            </h2>   
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($disparos as $disparo)
                    @php
                        $corFundo = match($disparo->status) {
                                'rodando' => 'bg-blue-50',
                                'concluido' => 'bg-green-50',
                                'erro' => 'bg-red-50',
                                'cancelado' => 'bg-yellow-50',
                                default => 'bg-gray-100',
                            };
                    @endphp

                    <div class="shadow-sm {{ $corFundo }} rounded-md p-4 relative group transition hover:shadow-md">
                        {{-- ID + Status --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-bold text-gray-700">
                            <strong>Título:</strong>
                                {{ $disparo->titulo }}
                            </div>

                            {{-- STATUS COM SWITCH --}}
                            @switch($disparo->status)
                                @case('rodando')
                                    <div class="flex items-center gap-2 text-blue-600 text-sm">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                                            </path>
                                        </svg>
                                        Rodando
                                    </div>
                                    @break

                                @case('concluido')
                                    <div class="flex items-center gap-2 text-green-600 text-sm">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Concluído
                                    </div>
                                    @break

                                @case('erro')
                                    <div class="flex items-center gap-2 text-red-600 text-sm">
                                        <i class="bi bi-x-circle-fill"></i>
                                        Falhou
                                    </div>
                                    @break
                                @case('cancelado')
                                    <div class="flex items-center gap-2 text-red-600 text-sm">
                                        <i class="bi bi-x-circle-fill"></i>
                                        Cancelado
                                    </div>
                                    @break
                            @endswitch
                        </div>

                        {{-- Mensagem --}}
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Mensagem:</strong><br>
                            {{ Str::limit($disparo->modelo_mensagem, 100) }}
                        </p>

                        {{-- Números + Data --}}
                        <p class="text-xs text-gray-600">
                            Enviando para {{ is_array($disparo->numeros_enviados) ? count($disparo->numeros_enviados) : 0 }} número(s)
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Iniciado em: {{ $disparo->created_at->format('d/m/Y H:i') }}
                        </p>

                        {{-- Botão Editar --}}
                        <div class="absolute bottom-3 right-3">
                            <input type="hidden" class="id-disparo" value="{{ $disparo->id }}">

                            <button type="button"
                                onclick="abrirModalDisparo({{ $disparo->id }})"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium flex items-center gap-1">
                                <i class="bi bi-eye-fill"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div id="modalDisparo" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative">
        {{-- Botão fechar --}}
        <button onclick="fecharModalDisparo()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl">
            <i class="bi bi-x-lg"></i>
        </button>

        {{-- Título --}}
        <h2 class="text-xl font-bold text-blue-800 mb-4 flex items-center gap-2">
            <i class="bi bi-broadcast-pin"></i> Detalhes do Disparo
        </h2>

        {{-- Conteúdo --}}
        <input type="hidden" id="idDisparoSelecionado">

        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Título:</strong> <span id="modalTitulo"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus" class="capitalize"></span></p>
            <p><strong>Mensagem:</strong><br><span id="modalMensagem"></span></p>
            <p><strong>Qtd. de Números:</strong> <span id="modalNumeros"></span></p>
            <p class="text-xs text-gray-500"><strong>Data:</strong> <span id="modalData"></span></p>

            {{-- Toggle números --}}
            <div>
                <button type="button"class="flex items-center gap-1 text-xs text-orange-600 hover:text-orange-800 font-semibold" onclick="toggleNumerosEnviados()"><i id="setaToggle" class="bi bi-chevron-down transition-all"></i> 
                    Ver números enviados
                </button>

                <div id="listaNumeros" class="mt-2 space-y-1 max-h-40 overflow-y-auto hidden border-t pt-2">
                    {{-- Números inseridos via JS --}}
                </div>
            </div>
        </div>
        {{-- Ações --}}
        <div class="mt-6 flex justify-between">
            <button id="btnPararDisparo" onclick="pararDisparo()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                <i class="bi bi-stop-circle-fill"></i> Parar Disparo
            </button>
        </div>
    </div>
</div>


