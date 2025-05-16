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
                                default => 'bg-gray-100',
                            };
                    @endphp

                    <div class="shadow-sm {{ $corFundo }} rounded-md p-4 relative group transition hover:shadow-md">
                        {{-- ID + Status --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-bold text-gray-700">
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
                            @endswitch
                        </div>

                        {{-- Mensagem --}}
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Mensagem:</strong><br>
                            {{ Str::limit($disparo->modelo_mensagem, 100) }}
                        </p>

                        {{-- Números + Data --}}
                        <p class="text-xs text-gray-600">
                            Enviando para {{ is_array($disparo->numeros) ? count($disparo->numeros) : 0 }} número(s)
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Iniciado em: {{ $disparo->created_at->format('d/m/Y H:i') }}
                        </p>

                        {{-- Botão Editar --}}
                        <div class="absolute bottom-3 right-3">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
