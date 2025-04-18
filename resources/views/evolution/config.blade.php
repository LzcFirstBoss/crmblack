@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto mt-10 px-4 space-y-10">

        {{-- Se conectado --}}
        @if ($instancia && $instancia->status_conexao === 'CONNECTED')
            <div class="bg-white p-6 rounded-lg shadow border" id="status">
                <h3 class="text-2xl font-semibold text-green-500 mb-2 flex gap-2 items-center"><i class="bi bi-whatsapp"></i>
                    Whatsapp Conectado <span class="flex w-3 h-3 me-3 bg-green-500 rounded-full"></span></h3>
                <p class="text-gray-600 mb-4 text-sm">Instância <strong>{{ $instancia->nome }}</strong> está conectada ao
                    número: <strong>{{ $instancia->telefone ?? 'n/d' }}</strong></p>
                <button id="btnDesconectar"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded flex items-center justify-center gap-2 transition">
                    <i class="bi bi-power"></i>
                    Desconectar Instância
                </button>
            </div>
        @else
            {{-- Se desconectado --}}
            <div class="bg-white p-6 rounded-lg shadow border" id="status">
                <h3 class="text-2xl font-semibold text-gray-800 mb-2"><i class="bi bi-whatsapp"></i> Conectar Whatsapp</h3>
                <p class="text-gray-600 mb-4 text-sm">Clique no botão abaixo para gerar o QR Code e conectar seu número.</p>

                <button onclick="gerarQrCode()"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 rounded flex items-center justify-center gap-2 transition mb-4">
                    <i class="bi bi-qr-code"></i>
                    Gerar QR Code
                </button>

                <div id="qrcode_result"
                    class="mt-6 border-dashed border-2 border-gray-300 rounded-lg py-10 flex flex-col items-center text-gray-400 text-sm">
                    <i class="bi bi-qr-code text-3xl mb-2"></i>
                    QR Code será exibido aqui
                </div>
            </div>
        @endif

{{-- Lista de Bots (Cards) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($bots as $bot)
        <div class="bg-white p-6 rounded-lg shadow border flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-1">{{ $bot->nome }}</h3>
                <p class="text-gray-600 text-sm">{{ Str::limit($bot->prompt, 100) }}</p>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('bots.edit', $bot->id) }}"
                   class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
                <form action="{{ route('bots.destroy', $bot->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este bot?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                        <i class="bi bi-trash"></i> Deletar
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>


    </div>

    <script>
        let statusInterval = null;

        function gerarQrCode() {
            const box = document.getElementById('qrcode_result');
            box.innerHTML = 'Carregando QR Code...';

            fetch(`/evolution/conectar`)
                .then(res => res.json())
                .then(data => {
                    if (data.base64) {
                        box.innerHTML =
                            `<img src=\"${data.base64}\" class=\"w-64 h-64 object-contain rounded mx-auto\">`;
                        iniciarVerificacaoStatus();
                    } else {
                        box.innerHTML = `<p class=\"text-red-500\">Erro ao gerar QR Code.</p>`;
                    }
                });
        }

        function iniciarVerificacaoStatus() {
            statusInterval = setInterval(() => {
                fetch('/evolution/status')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'CONNECTED') {
                            clearInterval(statusInterval);

                            const box = document.getElementById('status');
                            box.innerHTML = `
        <div class="flex flex-col items-center justify-center text-green-600 text-center space-y-4">
            <div id="lottie-check" class="w-32 h-32"></div>
            <p class="font-semibold text-lg"><i class="bi bi-whatsapp"></i> Whatsapp conectada com sucesso!</p>
        </div>
    `;

                            // Roda animação Lottie
                            lottie.loadAnimation({
                                container: document.getElementById('lottie-check'),
                                renderer: 'svg',
                                loop: false,
                                autoplay: true,
                                path: 'https://assets2.lottiefiles.com/packages/lf20_jbrw3hcz.json' // animação de check verde
                            });

                            setTimeout(() => location.reload(), 2500); // recarrega depois da animação
                        }
                    });
            }, 3000);
        }

        document.getElementById('btnDesconectar').addEventListener('click', () => {
            if (!confirm('Tem certeza que deseja desconectar a instância?')) return;

            const btn = document.getElementById('btnDesconectar');
            const output = document.getElementById('status');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            btn.disabled = true;
            output.classList.remove('hidden');
            output.innerHTML = `
            <div class="flex items-center justify-center gap-2 text-gray-600 text-sm animate-pulse">
                <i class="bi bi-arrow-repeat animate-spin"></i> Desconectando...
            </div>
        `;

            fetch('/evolution/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'SUCCESS') {
                        output.innerHTML = `
                    <div class="flex items-center gap-2 text-green-600 font-medium">
                        <i class="bi bi-check-circle-fill text-xl"></i>
                        Instância desconectada com sucesso!
                    </div>
                `;
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        output.innerHTML = `
                    <div class="flex items-center gap-2 text-red-600 font-medium">
                        <i class="bi bi-x-circle-fill text-xl"></i>
                        Erro: ${data.response?.message || 'Não foi possível desconectar.'}
                    </div>
                `;
                    }
                })
                .catch(err => {
                    output.innerHTML = `
                <div class="text-red-600 font-medium">
                     Erro inesperado:
                    <pre class="bg-white border mt-2 p-2 rounded text-xs text-gray-800">${err}</pre>
                </div>
            `;
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    </script>
@endsection
