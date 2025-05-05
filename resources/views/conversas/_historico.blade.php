    @foreach ($mensagens as $msg)
    @php
        $isBot = $msg->bot;
        $isMe = $msg->enviado_por_mim;
        $isClient = !$isBot && !$isMe;

        $nomeUsuario = $msg->usuario?->name ?? null;
        $conteudo = $msg->mensagem_enviada;

        // Detecta o tipo de arquivo
        $ehImagem = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conteudo);
        $ehAudio = preg_match('/\.(mp3|ogg|wav|m4a|webm)$/i', $conteudo);
        $ehVideo = preg_match('/\.(mp4|webm|mov)$/i', $conteudo);

        $caminho = asset('storage/' . $conteudo);
    @endphp

    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} mb-3">
        <div class="max-w-[75%] p-3 rounded-xl shadow-sm text-sm
            {{ $isClient ? 'bg-gray-200 text-gray-800' : ($isBot ? 'bg-blue-100 text-blue-900' : 'bg-orange-100 text-orange-900') }}">

            @if ($ehImagem || $ehAudio || $ehVideo)
            <div class="{{ $ehImagem || $ehVideo ? 'media-clickable cursor-pointer' : '' }}" data-type="{{ $ehImagem ? 'image' : ($ehVideo ? 'video' : '') }}" data-src="{{ $caminho }}">
                @if ($ehImagem)
                <img src="{{ $caminho }}" loading="lazy" alt="Imagem" class="rounded-lg max-w-xs preview-media">
                @elseif ($ehAudio)
                <div class="custom-audio-player flex items-center gap-4 bg-gray-100 rounded-lg p-3">
                    <button class="play-pause text-orange-500 text-2xl"><i class="bi bi-play-fill"></i></button>
                    <div class="progress-bar flex-1 bg-gray-300 rounded h-2 cursor-pointer">
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
                {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-blue-500 underline">$1</a>', e($conteudo)) !!}
            </div>
        @endif
        

            <div class="text-[11px] text-right mt-1 text-gray-500">
                {{ \Carbon\Carbon::parse($msg->data_e_hora_envio)->format('d/m H:i') }}
                @if ($nomeUsuario)
                    <span class="ml-1 text-purple-600"><i class="bi bi-person"></i> {{ explode(' ', $nomeUsuario)[0] }}</span>
                @elseif ($isBot)
                    <span class="ml-1 text-blue-400"><i class="bi bi-robot"></i> Bot</span>
                @elseif ($isMe)
                    <span class="ml-1 text-orange-400"><i class="bi bi-person-circle"></i> Você</span>
                @else
                    <span class="ml-1 text-gray-400">👤 Cliente</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
