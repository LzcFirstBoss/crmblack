@foreach ($mensagens as $msg)
    @php
        $isBot = $msg->bot;
        $isMe = $msg->enviado_por_mim;
        $isClient = !$isBot && !$isMe;

        $nomeUsuario = null;
        if ($msg->usuario_id && $msg->usuario) {
            $nomeUsuario = explode(' ', $msg->usuario->name)[0];
        }

        $conteudo = $msg->mensagem_enviada;
        $ehImagem = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conteudo);
        $ehAudio = preg_match('/\.(mp3|ogg|wav|m4a|webm)$/i', $conteudo);
        $ehArquivo = preg_match('/^uploads\//i', $conteudo);
        $url = $ehArquivo ? asset('storage/' . $conteudo) : null;
    @endphp

    <div class="flex {{ $isMe || $isBot ? 'justify-end' : 'justify-start' }} mb-2" data-id="{{ $msg->id }}">
        <div class="max-w-[75%] px-4 py-2 rounded shadow-sm text-sm
            {{ $isClient ? 'bg-gray-200 text-gray-800' : ($isBot ? 'bg-blue-100 text-blue-900 border border-blue-300' : 'bg-orange-100 text-orange-900') }}">

            <div class="whitespace-pre-line break-words">
                @if ($ehImagem)
                    <div class="relative group cursor-pointer">
                        <img src="{{ $url }}" alt="Imagem"
                            class="border border-gray-300 rounded max-w-xs max-h-64 object-cover transition-transform duration-200 hover:scale-105"onclick="abrirModalImagem('{{ $url }}')">
                    </div>
                @elseif ($ehAudio)
                    <div class="flex items-center bg-[#FE7F32]/10 rounded-xl w-[270px] h-12 px-2 space-x-2"
                        style="align-items: center">
                        <button onclick="togglePlay(this)" class="play-pause-btn text-[#FE7F32] text-xl"><i
                                class="bi bi-play-fill"></i></button>
                        <audio preload="metadata" class="audio-player hidden">
                            <source src="{{ $url }}" type="audio/mpeg">
                            Seu navegador não suporta o player de áudio.
                        </audio>
                        <div class="relative flex-1 h-[6px] rounded-full bg-[#FE7F32]/30 overflow-hidden mt-[1px]">
                            <div
                                class="progress-bar absolute left-0 top-0 h-full bg-[#FE7F32] w-0 transition-all duration-100">
                            </div>
                        </div>

                        <span class="text-xs text-[#FE7F32] w-[40px] text-right">0:00</span>
                    </div>
                @elseif ($ehArquivo)
                    <a href="{{ $url }}" class="text-blue-600 underline break-all" target="_blank">📎
                        Arquivo</a>
                @else
                    {{ $conteudo }}
                @endif
            </div>

            <div class="text-[11px] text-right mt-1 text-gray-500">
                {{ \Carbon\Carbon::parse($msg->data_e_hora_envio)->format('d/m H:i:s') }}
                @if ($nomeUsuario)
                    <span class="ml-1 text-purple-600"><i class="bi bi-person"></i> {{ $nomeUsuario }}</span>
                @elseif ($isBot)
                    <span class="ml-1 text-blue-400"><i class="bi bi-robot"></i> Bot</span>
                @elseif ($isMe)
                    <span class="ml-1 text-orange-400"><i class="bi bi-person-circle"></i> Você</span>
                @else
                    <span class="ml-1 text-gray-500">👤 Cliente</span>
                @endif
            </div>
        </div>
    </div>
@endforeach
