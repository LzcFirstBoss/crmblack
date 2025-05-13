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

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} mb-2">
    <div class="max-w-[75%] p-3 rounded-xl text-sm shadow
        {{ $isBot ? 'bg-blue-100 text-black' : ($isMe ? 'bg-[#D9FDD3] text-black' : 'bg-white text-black') }}">
        
        @if ($ehImagem || $ehAudio || $ehVideo)
            <div class="mt-1">
                @if ($ehImagem)
                    <img src="{{ $caminho }}" alt="Imagem" class="rounded-lg max-w-xs">
                @elseif ($ehAudio)
                    <audio controls class="w-full mt-1 rounded">
                        <source src="{{ $caminho }}">
                        Seu navegador não suporta áudio.
                    </audio>
                @elseif ($ehVideo)
                    <video controls class="w-full mt-1 rounded">
                        <source src="{{ $caminho }}">
                        Seu navegador não suporta vídeo.
                    </video>
                @endif
            </div>
        @else
            <div class="whitespace-pre-line break-words">
                {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-blue-600 underline">$1</a>', e($conteudo)) !!}
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
