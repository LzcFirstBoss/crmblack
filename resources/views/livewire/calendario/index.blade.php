<div class="w-full h-full p-6">
    <div class="bg-white rounded-xl shadow-lg h-full flex flex-col overflow-hidden">

        <div class="flex justify-between items-center px-6 py-4" style="background-color: #1F1F1F; color: white;">
            <h2 class="text-xl font-bold">
                <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($startsAt)->translatedFormat('F \d\e Y') }}
            </h2>

            <div class="flex gap-2 items-center">
                <button wire:click="goToPreviousWeek"
                    class="bg-white text-black px-3 py-2 rounded-full shadow hover:bg-gray-100">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button wire:click="goToNextWeek"
                    class="bg-white text-black px-3 py-2 rounded-full shadow hover:bg-gray-100">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button wire:click="abrirCriacao"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-semibold flex items-center gap-2 px-4 py-2 rounded shadow transition">
                    <i class="bi bi-plus-circle-fill text-lg"></i> Criar Evento
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 bg-gray-100 text-center text-sm font-semibold text-gray-600 border-b">
            @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $day)
                <div class="py-3">{{ $day }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 flex-grow h-[calc(100vh-260px)]">
            @php $currentDate = \Carbon\Carbon::parse($gridStartsAt); @endphp
            @for ($i = 0; $i < $totalDays; $i++)
                @php
                    $isToday = $currentDate->isToday();
                    $dayEvents = $this->events()->filter(
                        fn($e) => \Carbon\Carbon::parse($e->inicio)->toDateString() === $currentDate->toDateString(),
                    );
                @endphp

                <div class="border p-2 relative {{ $isToday ? 'bg-orange-100 border-orange-300' : 'bg-white' }}">
                    <div
                        class="absolute top-2 right-2 text-xs font-semibold {{ $isToday ? 'text-orange-600' : 'text-gray-500' }}">
                        {{ $currentDate->day }}
                    </div>
                    <div class="mt-5 space-y-1">
                        @foreach ($dayEvents as $event)
                            @php $hora = \Carbon\Carbon::parse($event->inicio)->format('H:i'); @endphp
                            <div class="cursor-pointer bg-orange-500 text-white text-xs px-2 py-1 rounded shadow-sm truncate"
                                title="{{ $event->descricao ?? '' }}" wire:click="abrirDetalhes({{ $event->id }})">
                                <i class="bi bi-clock me-1"></i> {{ $hora }} - {{ $event->titulo }}
                            </div>
                        @endforeach
                    </div>
                </div>
                @php $currentDate->addDay(); @endphp
            @endfor
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative">

                {{-- Cabeçalho --}}
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                        @if ($modoCriar)
                            <i class="bi bi-calendar-plus text-orange-500"></i> Criar Evento
                        @elseif ($modoEdicao)
                            <i class="bi bi-pencil-square text-green-500"></i> Editar Evento
                        @else
                            <i class="bi bi-info-circle text-blue-500"></i> Detalhes do Evento
                        @endif
                    </h2>
                    <button wire:click="fecharModal" class="text-gray-400 hover:text-red-500 text-xl font-bold">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Modo CRIAR --}}
                @if ($modoCriar)
                    <form wire:submit.prevent="salvarEvento" class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título do Evento</label>
                            <input type="text" wire:model.defer="titulo" placeholder="Digite o título"
                                class="w-full border rounded px-3 py-2 focus:ring-orange-400" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea wire:model.defer="descricao" placeholder="Descreva o evento"
                                class="w-full border rounded px-3 py-2 focus:ring-orange-400"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Início do Evento</label>
                            <input type="datetime-local" wire:model.defer="inicio"
                                class="w-full border rounded px-3 py-2 focus:ring-orange-400" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fim do Evento</label>
                            <input type="datetime-local" wire:model.defer="fim"
                                class="w-full border rounded px-3 py-2 focus:ring-orange-400" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cliente</label>
                            <input type="text" wire:model.defer="numerocliente"
                                placeholder="Exemplo: 5564...@s.whatsapp.net"
                                class="w-full border rounded px-3 py-2 focus:ring-orange-400" required>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="submit"
                                class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                Salvar
                            </button>
                        </div>
                    </form>

                    {{-- Modo EDITAR --}}
                @elseif ($modoEdicao)
                    <form wire:submit.prevent="atualizarEvento" class="space-y-5">
                        {{-- Título --}}
                        <div>
                            <label class="block text-sm text-gray-700 font-medium mb-1">Título</label>
                            <div
                                class="flex items-center border rounded px-3 py-2 focus-within:ring-2 focus-within:ring-green-400">
                                <i class="bi bi-type text-gray-400 me-2"></i>
                                <input type="text" wire:model.defer="eventoSelecionado.titulo"
                                    placeholder="Título do evento" class="w-full outline-none text-sm" required>
                            </div>
                        </div>

                        {{-- Descrição --}}
                        <div>
                            <label class="block text-sm text-gray-700 font-medium mb-1">Descrição</label>
                            <div
                                class="flex items-start border rounded px-3 py-2 focus-within:ring-2 focus-within:ring-green-400">
                                <i class="bi bi-card-text text-gray-400 me-2 mt-1"></i>
                                <textarea wire:model.defer="eventoSelecionado.descricao" placeholder="Detalhes"
                                    class="w-full outline-none text-sm resize-none h-24"></textarea>
                            </div>
                        </div>

                        {{-- numero cliente --}}
                        <div>
                            <label class="block text-sm text-gray-700 font-medium mb-1">Número do Cliente</label>
                            <div
                                class="flex items-center border rounded px-3 py-2 focus-within:ring-2 focus-within:ring-green-400">
                                <i class="bi bi-phone text-gray-400 me-2"></i>
                                <input type="text" wire:model.defer="eventoSelecionado.numerocliente"
                                    class="w-full outline-none text-sm" placeholder="5564...@s.whatsapp.net" required>
                            </div>
                        </div>


                        {{-- Início e Fim --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 font-medium mb-1">Início</label>
                                <input type="datetime-local" wire:model.defer="eventoSelecionado.inicio"
                                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-green-400"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 font-medium mb-1">Fim</label>
                                <input type="datetime-local" wire:model.defer="eventoSelecionado.fim"
                                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-green-400"
                                    required>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="$set('modoEdicao', false)"
                                class="text-gray-600 hover:text-red-600 text-sm font-medium">Cancelar</button>
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded text-sm font-semibold shadow">
                                <i class="bi bi-check-circle me-1"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>


                    {{-- Modo DETALHES --}}
                @else
                    <div class="space-y-6">
                        <!-- Cartão de informações -->
                        <div class="bg-gray-50 border rounded-lg p-4 space-y-3 shadow-sm">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-type text-orange-500 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Título</p>
                                    <p class="text-base font-semibold text-gray-800">{{ $eventoSelecionado->titulo }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i class="bi bi-card-text text-orange-500 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Descrição</p>
                                    <p class="text-base text-gray-700">{{ $eventoSelecionado->descricao ?: '—' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i class="bi bi-calendar-event text-orange-500 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Início</p>
                                    <p class="text-base text-gray-800">
                                        {{ \Carbon\Carbon::parse($eventoSelecionado->inicio)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i class="bi bi-calendar-check text-orange-500 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Fim</p>
                                    <p class="text-base text-gray-800">
                                        {{ \Carbon\Carbon::parse($eventoSelecionado->fim)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($eventoSelecionado->numerocliente)
                            <div class="text-right">
                                <a href="{{ url('/conversar') }}?numero={{ str_replace('@s.whatsapp.net', '', $eventoSelecionado->numerocliente) }}"
                                    class="inline-flex items-center gap-2 text-sm px-4 py-2 border border-green-600 rounded text-green-600 hover:bg-green-600 hover:text-white transition">
                                    <i class="bi bi-whatsapp"></i> Abrir Conversa
                                </a>
                            </div>
                        @endif


                        <!-- Ações -->
                        <div class="flex justify-end gap-3">
                            <button wire:click="abrirEdicao"
                                class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50 transition">
                                <i class="bi bi-pencil-square me-1"></i> Editar
                            </button>
                            <button wire:click="excluirEvento"
                                class="px-4 py-2 text-sm font-medium text-red-600 border border-red-600 rounded hover:bg-red-50 transition">
                                <i class="bi bi-trash3 me-1"></i> Excluir
                            </button>
                            <button wire:click="fecharModal"
                                class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded hover:bg-gray-100 transition">
                                Fechar
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif
