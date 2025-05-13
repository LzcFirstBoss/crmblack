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
@endphp

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} mb-3 px-3">
    <div class="relative max-w-[65%] px-3 py-2 rounded-xl shadow-sm text-sm leading-snug text-black
        {{ $isClient ? 'bg-gray-200' : ($isBot ? 'bg-blue-100' : 'bg-orange-100') }}">
        
        {{-- Conteúdo: texto, imagem, áudio ou vídeo --}}
        @if ($ehImagem || $ehAudio || $ehVideo)
            <div class="{{ $ehImagem || $ehVideo ? 'media-clickable cursor-pointer' : '' }}"
                 data-type="{{ $ehImagem ? 'image' : ($ehVideo ? 'video' : '') }}"
                 data-src="{{ $caminho }}">
                @if ($ehImagem)
                    <img src="{{ $caminho }}" loading="lazy" alt="Imagem" class="rounded-lg max-w-xs preview-media">
                @elseif ($ehAudio)
                    <div class="custom-audio-player flex items-center gap-4 bg-white rounded-lg p-3 border border-gray-300">
                        <button class="play-pause text-orange-500 text-2xl"><i class="bi bi-play-fill"></i></button>
                        <div class="progress-bar flex-1 bg-gray-200 rounded h-2 cursor-pointer">
                            <div class="progress bg-orange-500 h-2 rounded" style="width: 0%;"></div>
                        </div>
                        <div class="time text-sm text-gray-600">0:00 / 0:00</div>
                    </div>
                @elseif ($ehVideo)
                    <video controls class="video-player w-full mt-2 rounded-lg bg-black">
                        <source src="{{ $caminho }}">
                        Seu navegador não suporta vídeo.
                    </video>
                @endif
            </div>
        @else
            <div class="whitespace-pre-line break-words">
                {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-blue-600 underline hover:text-blue-800 transition">$1</a>', e($conteudo)) !!}
            </div>
        @endif

        {{-- Rodapé com horário e autor --}}
        <div class="text-[11px] text-right mt-2 text-gray-500 flex items-center justify-end gap-1">
            <span>{{ \Carbon\Carbon::parse($msg->data_e_hora_envio)->format('d/m H:i') }}</span>
            @if ($nomeUsuario)
                <span class="text-purple-600"><i class="bi bi-person"></i> {{ explode(' ', $nomeUsuario)[0] }}</span>
            @elseif ($isBot)
                <span class="text-blue-400"><i class="bi bi-robot"></i> Bot</span>
            @elseif ($isMe)
                <span class="text-orange-400"><i class="bi bi-person-circle"></i> Você</span>
            @else
                <span class="text-gray-400">👤 Cliente</span>
            @endif
        </div>
    </div>
</div>
@endforeach
