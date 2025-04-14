@foreach ($mensagens as $msg)
    @php
        $isBot = $msg->bot;
        $isMe = $msg->enviado_por_mim;
        $isClient = !$isBot && !$isMe;

        $nomeUsuario = null;
        if ($msg->usuario_id && $msg->usuario) {
            $nomeUsuario = explode(' ', $msg->usuario->name)[0]; // Pega o primeiro nome
        }
    @endphp

    <div 
        class="flex {{ $isMe || $isBot ? 'justify-end' : 'justify-start' }}"
        data-id="{{ $msg->id }}"
    >
        <div class="max-w-[75%] px-4 py-2 rounded shadow-sm text-sm
            {{ $isClient ? 'bg-gray-200 text-gray-800' : ($isBot ? 'bg-blue-100 text-blue-900 border border-blue-300' : 'bg-orange-100 text-orange-900') }}">
            
            <div class="whitespace-pre-line">
                {{ $msg->mensagem_enviada }}
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
