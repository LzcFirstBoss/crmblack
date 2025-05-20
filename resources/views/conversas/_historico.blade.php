@foreach ($mensagens as $msg)
@php
    $isBot = $msg->bot;
    $isMe = $msg->enviado_por_mim;
    $isClient = !$isBot && !$isMe;

    $nomeUsuario = $msg->usuario?->name ?? null;
    $conteudo = $msg->mensagem_enviada;

    $ehImagem = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conteudo);
    $ehAudio = preg_match('/\.(mp3|ogg|wav|m4a|webm)$/i', $conteudo);
    $ehVideo = preg_match('/\.(mp4|webm|mov)$/i', $conteudo);

    $caminho = asset('storage/' . $conteudo);

    // Cores no estilo Apple / WhatsApp moderno
    $corHex = $isClient ? '#d7d7d7' : ($isBot ? '#d0e8ff' : '#94ffc3');
    $alinhamento = $isMe ? 'justify-end items-end' : 'justify-start items-start';
    $setaStyle = $isMe
        ? "right: -6px; border-left-color: $corHex"
        : "left: -6px; border-right-color: $corHex";
@endphp

<div class="flex {{ $alinhamento }} mb-1 px-3">
    <div class="flex flex-col max-w-[65%] {{ $isMe ? 'items-end' : 'items-start' }}">

        {{-- Balão --}}
        <div class="relative rounded-2xl text-[15px] shadow-lg font-normal leading-relaxed text-black px-4 min-w-[120px] w-fit"
             style="position: relative; background-color: {{ $corHex }}; padding-top: 6px; padding-bottom: 4px;">

            {{-- Setinha --}}
            <div style="content: ''; position: absolute; top: 12px; {{ $setaStyle }};
                        width: 0; height: 0;
                        border-top: 6px solid transparent;
                        border-bottom: 6px solid transparent;
                        {{ $isMe ? 'border-left' : 'border-right' }}: 6px solid {{ $corHex }};">
            </div>

            {{-- Conteúdo --}}
            @if ($ehImagem || $ehAudio || $ehVideo)
                <div class="{{ $ehImagem || $ehVideo ? 'media-clickable cursor-pointer' : '' }}"
                     data-type="{{ $ehImagem ? 'image' : ($ehVideo ? 'video' : '') }}"
                     data-src="{{ $caminho }}">
                    @if ($ehImagem)
                        <img src="{{ $caminho }}" loading="lazy" alt="Imagem" class="rounded-xl max-w-xs preview-media">
                    @elseif ($ehAudio)
                        <div class="custom-audio-player flex items-center gap-4 bg-white rounded-lg p-3 border border-gray-300">
                            <button class="play-pause text-green-500 text-2xl"><i class="bi bi-play-fill"></i></button>
                            <div class="progress-bar flex-1 bg-gray-200 rounded h-2 cursor-pointer">
                                <div class="progress bg-green-500 h-2 rounded" style="width: 0%;"></div>
                            </div>
                            <div class="time text-sm text-gray-600">0:00 / 0:00</div>
                        </div>
                    @elseif ($ehVideo)
                        <video controls class="video-player w-full mt-2 rounded-xl bg-black">
                            <source src="{{ $caminho }}">
                            Seu navegador não suporta vídeo.
                        </video>
                    @endif
                </div>
                @else
                    <div class="break-words leading-tight text-[15px] font-normal text-black">
                        {!! nl2br(
                            preg_replace(
                                '/(https?:\/\/[^\s]+)/',
                                '<a href="$1" target="_blank" class="text-blue-600 underline hover:text-blue-800 transition">$1</a>',
                                e($conteudo)
                            )
                        ) !!}
                    </div>
                @endif

            {{-- Horário discreto --}}
            <div class="text-[9px] text-gray-500 text-right mt-[3px]">
                {{ \Carbon\Carbon::parse($msg->data_e_hora_envio)->format('d/m H:i') }}
            </div>
        </div>

        {{-- Identificador --}}
        <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-1">
            @if ($nomeUsuario)
                <span class="flex items-center gap-1 bg-purple-100 text-purple-700 px-2 py-[2px] rounded-full border border-purple-300">
                    <i class="bi bi-person"></i> {{ explode(' ', $nomeUsuario)[0] }}
                </span>
            @elseif ($isBot)
                <span class="flex items-center gap-1 bg-blue-100 text-blue-700 px-2 py-[2px] rounded-full border border-blue-300">
                    <i class="bi bi-robot"></i> Bot
                </span>
            @elseif ($isMe)
                <span class="flex items-center gap-1 bg-green-100 text-green-700 px-2 py-[2px] rounded-full border border-green-300">
                    <i class="bi bi-person-circle"></i> Você
                </span>
            @else
                <span class="flex items-center gap-1 bg-gray-200 text-gray-700 px-2 py-[2px] rounded-full border border-gray-400">
                    <i class="bi bi-person"></i> Cliente
                </span>
            @endif
        </div>
    </div>
</div>
@endforeach
