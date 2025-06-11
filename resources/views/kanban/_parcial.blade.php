<div class="overflow-x-auto w-full pb-4">
    <div class="flex gap-4" id="kanban-colunas">
        @foreach ($colunas as $coluna)
            <div class="kanban-column-wrapper rounded shadow" style="background-color: {{ $coluna->cor }}20; border-top: 5px solid {{ $coluna->cor }};">
                <!-- TOPO -->
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

                <!-- CARDS -->
                <div class="kanban-column min-h-[550px] max-h-[550px] overflow-y-auto space-y-2 p-3" id="status-{{ $coluna->id }}">
                    @foreach ($mensagens[$coluna->id] ?? [] as $mensagem)
                        <div class="kanban-card" data-id="{{ $mensagem->id }}" data-numero="{{ $mensagem->numero_cliente }}">
                            <div class="kanban-card-header">
                                <div class="flex items-center justify-between">
                                    <a href="{{ url('/conversar') }}?numero={{ $mensagem->numero_cliente }}" class="hover:underline text-orange-600 text-xs font-semibold" target="_blank">
                                        <i class="bi bi-whatsapp"></i> {{ $mensagem->numero_cliente }} - {{ $mensagem->cliente->nome }}
                                    </a>
                                    <div id="bolinha-{{ $mensagem->numero_cliente }}">
                                        @if($mensagem->cliente?->qtd_mensagens_novas > 0)
                                            <span class="notificacao-bolinha ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                                {{ $mensagem->cliente->qtd_mensagens_novas }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php
                                $preview = $mensagem->mensagem_enviada;

                                if ($mensagem->bot) {
                                    $remetente = "Bot";
                                } elseif ($mensagem->enviado_por_mim) {
                                    $remetente = $mensagem->usuario ? "({$mensagem->usuario->name})" : "Usuário";
                                } else {
                                    $remetente = "Cliente";
                                }

                                if (preg_match('/uploads\/.*\.(jpg|jpeg|png|gif)$/i', $preview)) {
                                    $preview = '<i class="bi bi-card-image"></i> Imagem';
                                } elseif (preg_match('/uploads\/.*\.(mp3|ogg|wav|webm)$/i', $preview)) {
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
    </div>
</div>
