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

    $corHex = $isClient ? '#d7d7d7' : ($isBot ? '#d0e8ff' : '#D9FDD3');
    $setaStyle = $isMe
        ? "right: -6px; border-left-color: $corHex"
        : "left: -6px; border-right-color: $corHex";

    $resposta = $msg->mensagem_respondida_id
        ? \App\Models\Webhook\Mensagem::find($msg->mensagem_respondida_id)
        : null;
@endphp

<div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} flex-row items-start gap-1 px-3 group mb-1">
    {{-- Botão de opções --}}
    @if (($isMe && $msg->id_mensagem && $msg->status !== 'apagado') || $isClient)
        <div class="{{ $isMe ? 'order-1' : 'order-2' }} group-hover:block hidden">
            <div class="relative">
                <button onclick="toggleDropdown(this)" class="rounded-full p-1 hover:bg-gray-200 transition" title="Mais opções">
                    <i class="bi bi-three-dots text-gray-600 text-base"></i>
                </button>
                <div class="dropdown-options absolute {{ $isMe ? 'left-0' : 'right-0' }} mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden w-36 z-50 overflow-hidden">
                    <button onclick="responderMensagem('{{ $msg->id }}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="bi bi-reply mr-2"></i> Responder
                    </button>
                    @if ($isMe && now()->lessThan(\Carbon\Carbon::parse($msg->data_e_hora_envio)->addMinutes(15)))
                        <button onclick="editarMensagem('{{ $msg->id_mensagem }}', '{{ e($msg->mensagem_enviada) }}')" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="bi bi-pencil-square mr-2"></i> Editar
                        </button>
                        <button onclick="apagarMensagem('{{ $msg->id_mensagem }}')" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-100">
                            <i class="bi bi-trash3 mr-2"></i> Apagar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Balão de mensagem --}}
    <div class="{{ $isMe ? 'order-2' : 'order-1' }} flex flex-col max-w-[65%] {{ $isMe ? 'items-end' : 'items-start' }}"
         data-idmsg="{{ $msg->id }}"
         data-texto="{{ e(Str::limit($msg->mensagem_enviada, 100)) }}"
         data-numero="{{ $msg->numero_cliente }}"
         id="mensagem-{{ $msg->id }}"
         data-fromme="{{ $isMe ? 'true' : 'false' }}">
        <div class="msg relative group rounded-2xl text-[15px] shadow-lg font-normal leading-relaxed text-black px-4 min-w-[120px] w-fit"
             style="background-color: {{ $corHex }}; padding-top: 6px; padding-bottom: 4px;">

            {{-- Setinha --}}
            <div style="content: ''; position: absolute; top: 12px; {{ $setaStyle }};
                        width: 0; height: 0;
                        border-top: 6px solid transparent;
                        border-bottom: 6px solid transparent;
                        {{ $isMe ? 'border-left' : 'border-right' }}: 6px solid {{ $corHex }};">
            </div>

            {{-- Mensagem respondida visual --}}
          @if ($resposta)
    <div onclick="scrollParaMensagem('{{ $resposta->id }}')"
         class="mb-2 px-2 py-1 rounded-md bg-[#D1F4CC] border-l-4 border-orange-400 cursor-pointer hover:bg-orange-100 transition">
        <div class="text-xs font-semibold {{ $isMe ? 'text-right' : '' }} text-orange-500">
            {{ $resposta->enviado_por_mim ? 'Você' : 'Cliente' }}
        </div>
        <div class="text-sm text-gray-700 truncate">
            {{ Str::limit(strip_tags($resposta->mensagem_enviada), 100) }}
        </div>
    </div>
@endif


            {{-- Conteúdo da mensagem --}}
            @php
                $ehArquivo = preg_match('/\.(pdf|doc|docx|txt|xls|xlsx|zip|rar|csv)$/i', $conteudo);
                $nomeArquivo = basename($conteudo);
                $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
            @endphp

@if ($isMe && $msg->status === 'apagado')
    <div class="flex flex-col gap-1">
        <div class="break-words leading-tight text-[15px] font-normal text-gray-500 line-through">
            {!! nl2br(e($conteudo)) !!}
        </div>
        <div class="flex items-center gap-2 text-sm text-red-600 italic">
            <i class="bi bi-x-circle"></i>
            Esta mensagem foi apagada para todos.
        </div>
    </div>
@elseif ($isMe && $msg->status === 'erro')
    <div class="flex flex-col gap-1">
        <div class="break-words leading-tight text-[15px] font-normal text-red-600">
            {!! nl2br(e($conteudo)) !!}
        </div>
        <div class="flex items-center gap-2 text-sm text-red-500 italic">
            <i class="bi bi-exclamation-circle"></i>
            Erro ao enviar mensagem.
        </div>
    </div>

            @elseif ($ehImagem || $ehAudio || $ehVideo || $ehArquivo)
                <div class="{{ ($ehImagem || $ehVideo) ? 'media-clickable cursor-pointer' : '' }}"
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
                    @elseif ($ehArquivo)
                        <a href="{{ $caminho }}" download
                           class="flex items-center gap-3 bg-white border border-gray-300 rounded-xl p-3 shadow-sm w-full max-w-xs hover:bg-gray-100 transition"
                           title="Baixar arquivo">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-text text-3xl text-gray-600"></i>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $nomeArquivo }}</p>
                                <p class="text-xs text-gray-500">{{ strtoupper($extensao) }} • Arquivo</p>
                            </div>
                            <div class="text-xl">
                                <i class="bi bi-arrow-down-circle"></i>
                            </div>
                        </a>
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

            <div class="text-[9px] text-gray-500 text-right mt-[3px]">
                {{ \Carbon\Carbon::parse($msg->data_e_hora_envio)->format('d/m H:i') }}
            </div>
        </div>

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
