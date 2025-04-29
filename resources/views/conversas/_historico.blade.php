@foreach ($mensagens as $msg)
    @php
        $isBot = $msg->bot;
        $isMe = $msg->enviado_por_mim;
        $isClient = !$isBot && !$isMe;

        $nomeUsuario = $msg->usuario?->name ?? null;
        $conteudo = $msg->mensagem_enviada;
        $ehImagem = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $conteudo);
        $ehAudio = preg_match('/\.(mp3|ogg|wav|m4a)$/i', $conteudo);
    @endphp

    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} mb-3">
        <div class="max-w-[75%] p-3 rounded-xl shadow-sm text-sm
            {{ $isClient ? 'bg-gray-200 text-gray-800' : ($isBot ? 'bg-blue-100 text-blue-900' : 'bg-orange-100 text-orange-900') }}">

            @if ($ehImagem)
                <img src="{{ asset('storage/' . $conteudo) }}" loading="lazy" alt="Imagem" class="rounded-lg max-w-xs">
            @elseif ($ehAudio)
                <audio controls class="w-full mt-2">
                    <source src="{{ asset('storage/' . $conteudo) }}" type="audio/mpeg">
                    Seu navegador não suporta áudio.
                </audio>
            @else
                <div class="whitespace-pre-line break-words">{{ $conteudo }}</div>
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
