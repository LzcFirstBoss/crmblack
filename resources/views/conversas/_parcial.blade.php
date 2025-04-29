@foreach ($contatos as $contato)
    <div id="contato-{{ $contato->numero_cliente }}" onclick="abrirConversa('{{ $contato->numero_cliente }}')" class="contato flex items-center p-4 border-b cursor-pointer hover:bg-orange-100 transition">
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <div class="font-semibold text-gray-800 numero-cliente">{{ $contato->numero_cliente }}</div>

                @if($contato->qtd_mensagens_novas > 0)
                <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-orange-500 rounded-full">
                    {{ $contato->qtd_mensagens_novas }}
                </span>
            @endif
            </div>
            <div class="text-xs text-gray-500 truncate">
                {{ Str::limit($contato->mensagem_enviada, 40) }}
            </div>
        </div>
        <div class="text-xs text-gray-400 whitespace-nowrap ml-2">
            {{ \Carbon\Carbon::parse($contato->data_e_hora_envio)->format('H:i') }}
        </div>
    </div>
@endforeach
