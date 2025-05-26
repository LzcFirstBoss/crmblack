@foreach ($contatos as $contato)
    @php
        $preview = $contato->mensagem_enviada;
        $icone = '';

        if (Str::startsWith($preview, 'uploads/') && Str::endsWith($preview, ['.jpg', '.jpeg', '.png', '.gif'])) {
            $icone = '<i class="bi bi-card-image"></i> Imagem';
        } elseif (Str::startsWith($preview, 'uploads/') && Str::endsWith($preview, ['.mp3', '.ogg', '.wav', '.webm'])) {
            $icone = '<i class="bi bi-music-note-beamed"></i> Áudio';
        } elseif (Str::startsWith($preview, 'uploads/') && Str::endsWith($preview, ['.mp4', '.mov', '.avi'])) {
            $icone = '<i class="bi bi-camera-reels"></i> Vídeo';
        } elseif (Str::startsWith($preview, 'uploads/') && Str::endsWith($preview, ['.txt', '.pdf', '.word', 'df', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'zip', 'rar', 'csv'])) {
            $icone = '<i class="bi bi-file-earmark"></i> Documento';
        }
    @endphp

    <div id="contato-{{ $contato->numero_cliente }}"
     onclick="abrirConversa('{{ $contato->numero_cliente }}')"
     class="contato flex items-center p-4 border-b cursor-pointer hover:bg-orange-100 transition"
     data-lido="{{ $contato->qtd_mensagens_novas == 0 ? 'sim' : 'nao' }}">
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold text-gray-800 numero-cliente">
                        {{ $contato->nome_cliente ?? 'Sem nome' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $contato->numero_cliente }}
                    </div>
                </div>

                @if($contato->qtd_mensagens_novas > 0)
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-orange-500 rounded-full">
                        {{ $contato->qtd_mensagens_novas }}
                    </span>
                @endif
            </div>

            <div class="text-xs text-gray-500 truncate mt-1">
                {!! $icone ?: Str::limit($preview, 40) !!}
            </div>
        </div>
        <div class="text-xs text-gray-400 whitespace-nowrap ml-2">
            {{ \Carbon\Carbon::parse($contato->data_e_hora_envio)->format('H:i') }}
        </div>
    </div>
@endforeach
