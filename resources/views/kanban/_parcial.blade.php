@foreach ($colunas as $coluna)
    <div class="bg-white rounded shadow p-4 min-w-[300px] w-full kanban-column" style="border-top: 5px solid {{ $coluna->cor }};">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold text-lg">{{ $coluna->nome }}</h2>
            @if (!$coluna->fixo)
                <div class="tolls">
                    <button class="text-red-500 text-xs" onclick="removerStatus({{ $coluna->id }})">
                        <i class="bi bi-trash3"></i>
                    </button>
                        <input type="color" value="{{ $coluna->cor }}" onchange="atualizarCor({{ $coluna->id }}, this.value)" class="w-6 h-6 cursor-pointer" title="Editar cor">
                </div>
            @endif
        </div>

        <div class="kanban-column min-h-[200px] space-y-2" id="status-{{ $coluna->id }}">
            @foreach ($mensagens[$coluna->id] ?? [] as $mensagem)
            <div class="p-3 bg-gray-100 rounded shadow text-sm cursor-move" data-id="{{ $mensagem->id }}">
                <div class="font-bold text-xs text-gray-600 mb-1">
                    <a href="{{ route('kanban.historico', $mensagem->numero_cliente) }}" class="hover:underline text-orange-600">
                        {{ $mensagem->numero_cliente }}
                    </a>
                </div>
                <div>{{ Str::limit($mensagem->mensagem_enviada, 100) }}</div>
                <div class="text-right text-xs text-gray-400 mt-1">
                    {{ $mensagem->created_at->format('d/m H:i:s') }}
                </div>
            </div>            
            @endforeach
        </div>
    </div>
@endforeach
