@foreach ($colunas as $coluna)
<div class="rounded shadow min-w-[300px] w-full kanban-column-wrapper" style="background-color: {{ $coluna->cor }}20; border-top: 5px solid {{ $coluna->cor }};">

    <!-- TOPO BRANCO -->
    <div class="bg-white rounded-t p-3 flex justify-between items-center">
        <h2 class="font-semibold text-lg">{{ $coluna->nome }}</h2>
        @if (!$coluna->fixo)
            <div class="tolls flex items-center space-x-2">
                <button class="text-red-500 text-xs" onclick="removerStatus({{ $coluna->id }})">
                    <i class="bi bi-trash3"></i>
                </button>
                <input type="color" value="{{ $coluna->cor }}" onchange="atualizarCor({{ $coluna->id }}, this.value)" class="color-picker" title="Editar cor">
            </div>
        @endif
    </div>

    <!-- ÁREA DOS CARDS COM FUNDO COLORIDO SUAVE -->
    <div class="kanban-column min-h-[200px] space-y-2 p-3" id="status-{{ $coluna->id }}">
        @foreach ($mensagens[$coluna->id] ?? [] as $mensagem)
        <div class="kanban-card" data-id="{{ $mensagem->id }}">
            <div class="kanban-card-header">
                <a href="{{ url('/conversar') }}?numero={{ $mensagem->numero_cliente }}" class="hover:underline text-orange-600 text-xs font-semibold" target="_blank">
                    <i class="bi bi-whatsapp"></i>    {{ $mensagem->numero_cliente }} 
                </a>                
            </div>
            @php
            $preview = $mensagem->mensagem_enviada;
            
            // Definir remetente
            if ($mensagem->bot) {
                $remetente = "Bot";
            } elseif ($mensagem->enviado_por_mim) {
                // Verifica se tem usuário associado
                if ($mensagem->usuario) {
                    $remetente = "({$mensagem->usuario->name})";
                } else {
                    $remetente = "Usuário";
                }
            } else {
                $remetente = "Cliente";
            }
            
            // Definir o preview baseado no conteúdo
            if (preg_match('/uploads\/.*\.(jpg|jpeg|png|gif)$/i', $preview)) {
                $preview = '<i class="bi bi-card-image"></i> Imagem';
            } elseif (preg_match('/uploads\/.*\.(mp3|ogg|wav)$/i', $preview)) {
                $preview = '<i class="bi bi-music-note-beamed"></i> Áudio';
            } elseif (preg_match('/uploads\/.*\.(mp4|mov|avi)$/i', $preview)) {
                $preview = '<i class="bi bi-camera-reels"></i> Vídeo';
            } elseif (preg_match('/uploads\/.*\.(pdf|docx?|xlsx?)$/i', $preview)) {
                $preview = '<i class="bi bi-file-earmark-text"></i> Documento';
            } else {
                $preview = e(Str::limit($preview, 100));
            }
            @endphp
            
            <div class="kanban-card-body">
                <strong>{{ $remetente }}:</strong> {!! $preview !!}
            </div>
            
            <div class="kanban-card-footer text-right text-xs text-gray-400 mt-2">
                {{ $mensagem->created_at->format('d/m H:i:s') }}
            </div>
        </div>
                 
        @endforeach
    </div>
</div>
@endforeach
