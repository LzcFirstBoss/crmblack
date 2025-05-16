@extends('layouts.dashboard')
<title>Zabulon - Disparo</title>

<style>
@keyframes fade {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade {
    animation: fade 0.3s ease-out;
}
</style>

@section('content')
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow p-6 mt-8" x-data="{ etapa: 1, enviando: false }">


        <form method="POST" action="{{ route('disparo.enviar') }}" @submit.prevent="if (!enviando) { enviando = true; $el.submit(); }">

            @csrf

            {{-- Título e descrição --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-orange-800">Criar Disparo de Mensagem</h1>
                <p class="text-gray-600 text-sm">Crie sua mensagem, selecione os destinatários e envie ou agende o disparo
                </p>
            </div>

            {{-- Navegação de etapas --}}
            <div class="flex border-b border-gray-200 mb-6">
                <button type="button"
                    :class="etapa === 1 ? 'border-b-4 border-orange-600 text-orange-600 font-semibold' :
                        'text-gray-500 hover:text-orange-600'"
                    class="flex-1 text-center py-2" @click.prevent="etapa = 1">
                    Modelo de Mensagem
                </button>
                <button type="button"
                    :class="etapa === 2 ? 'border-b-4 border-orange-600 text-orange-600 font-semibold' :
                        'text-gray-500 hover:text-orange-600'"
                    class="flex-1 text-center py-2" @click.prevent="etapa = 2">
                    Contatos
                </button>
            </div>

            {{-- ETAPA 1 - Modelo de Mensagem --}}
            <div x-show="etapa === 1" class="space-y-4">
                <div>
                    <label class="font-medium block mb-1">Título do disparo</label>
                    <input type="text" name="titulo" class="w-full border rounded px-3 py-2 resize-none"
                        placeholder="Título do envio max(100)." maxlength="100" required></input>
                </div>

                <div>
                    <label class="font-medium block mb-1">Conteúdo da mensagem</label>
                    <textarea name="mensagem" class="w-full border rounded px-3 py-3 h-40 resize-none"
                        placeholder="Digite o conteúdo da sua mensagem aqui..." required></textarea>
                </div>

            <div class="bg-red-50 text-red-800 p-4 rounded text-sm">
                <strong>Importante:</strong> <i class="bi bi-exclamation-triangle"></i> Os disparos são processados em tempo real e podem demorar de acordo com a quantidade de contatos selecionados. As mensagens enviadas são personalizadas com base no conteúdo que você escreveu aqui.
            </div>


                <div class="flex justify-end mt-6">
                    <button type="button" class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700"
                        @click="etapa = 2">Próximo</button>
                </div>
            </div>

            {{-- ETAPA 2 - Contatos --}}
            <div x-show="etapa === 2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Contatos --}}
                    <div>
                        <h3 class="text-lg font-semibold mb-2"><i class="bi bi-search"></i> Selecionar Contatos </h3>

                        <input type="text" placeholder=" Buscar contatos..."
                            class="w-full border rounded px-3 py-2 mb-3">

                        <label class="flex items-center space-x-2 mb-2">
                            <input type="checkbox">
                            <span>Selecionar todos ({{ count($clientes) }})</span>
                        </label>

                        <div class="border rounded p-3 max-h-[280px] overflow-y-auto space-y-2 text-sm">
                            @foreach ($clientes as $cliente)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="clientes[]" value="{{ $cliente->telefoneWhatsapp }}">
                                    <div>
                                        <div class="font-semibold">{{ $cliente->nome }}</div>
                                        <div class="text-gray-500">{{ $cliente->telefoneWhatsapp }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="text-sm text-gray-500 mt-2" id="contador-contatos">
                            0 de {{ count($clientes) }} contatos selecionados
                        </div>

                    </div>

                    {{-- Funis --}}
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Ou Selecionar Funil</h3>

                        <div class="border-l-2 border-orange-500 pl-3 mb-4">
                            <div class="text-sm font-medium text-black-800"><i class="bi bi-funnel"></i> Funis Disponíveis
                            </div>
                        </div>
                        <div class="space-y-3 max-h-[200px] overflow-y-auto pr-1">
                            @foreach ($funis as $funil)
                                @php
                                    $total = $contagemPorFunil[$funil->id] ?? 0;
                                @endphp
                                <label class="block border rounded p-3 cursor-pointer hover:shadow transition"
                                    style="border-color: {{ $funil->cor }}">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <input type="radio" name="funil" class="mr-2" value="{{ $funil->id }}">
                                            <span class="font-semibold">{{ $funil->nome }}</span>
                                            <div class="text-sm text-gray-500">Clientes com esse status</div>
                                        </div>
                                        <div class="text-xs bg-gray-200 px-2 py-1 rounded">{{ $total }} contatos
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="text-sm text-orange-600 mt-3">
                            Funil selecionado substituirá a seleção individual de contatos.
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400"
                        @click="etapa = 1">Voltar</button>
<button 
    type="submit" 
    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 flex items-center gap-2"
    :disabled="enviando"
    x-bind:class="{ 'opacity-60 cursor-not-allowed': enviando }"
>
    <template x-if="!enviando">
        <span>Enviar Disparo</span>
    </template>
    <template x-if="enviando">
        <span class="flex items-center">
            <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="white"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                </path>
            </svg>
            Enviando...
        </span>
    </template>
</button>

                </div>
            </div>
        </form>

        @if (session('sucesso'))
    <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded shadow-lg z-50 animate-fade"x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show">
        <i class="bi bi-arrow-clockwise"></i> {{ session('sucesso') }}
    </div>
@endif

@if (session('erro'))
    <div class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-3 rounded shadow-lg z-50 animate-fade"x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show">
        <i class="bi bi-x-lg"></i> {{ session('erro') }}
    </div>
@endif
    </div>

    @include('disparo._parcialativos')


    {{-- AlpineJS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contatoCheckboxes = document.querySelectorAll('input[name="clientes[]"]');
            const funilRadios = document.querySelectorAll('input[name="funil"]');
            const contadorTexto = document.querySelector('#contador-contatos');

            function atualizarContador() {
                const selecionados = document.querySelectorAll('input[name="clientes[]"]:checked').length;
                const total = contatoCheckboxes.length;
                contadorTexto.textContent = `${selecionados} de ${total} contatos selecionados`;
            }

            contatoCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        funilRadios.forEach(radio => radio.checked = false);
                    }
                    atualizarContador();
                });
            });

            funilRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        contatoCheckboxes.forEach(checkbox => checkbox.checked = false);
                        atualizarContador();
                    }
                });
            });

            atualizarContador(); // inicia já com o valor certo
        });
    </script>
    <script>
function toggleNumerosEnviados() {
    const lista = document.getElementById('listaNumeros');
    const seta = document.getElementById('setaToggle');

    if (lista.classList.contains('hidden')) {
        lista.classList.remove('hidden');
        seta.classList.remove('bi-chevron-down');
        seta.classList.add('bi-chevron-up');
    } else {
        lista.classList.add('hidden');
        seta.classList.remove('bi-chevron-up');
        seta.classList.add('bi-chevron-down');
    }
}

function abrirModalDisparo(id) {
    fetch(`/disparo/${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('idDisparoSelecionado').value = data.id;
            document.getElementById('modalTitulo').textContent = data.titulo;
            document.getElementById('modalStatus').textContent = data.status;
            document.getElementById('modalMensagem').textContent = data.modelo_mensagem;
            document.getElementById('modalNumeros').textContent = Array.isArray(data.numeros_enviados)
                ? data.numeros_enviados.length
                : 0;
            document.getElementById('modalData').textContent = new Date(data.created_at).toLocaleString();

            // Gerar lista de números com links
            const lista = document.getElementById('listaNumeros');
            lista.innerHTML = ''; // limpar lista anterior
            if (Array.isArray(data.numeros_enviados)) {
                data.numeros_enviados.forEach(numero => {
                    const link = document.createElement('a');
                    const numeroLimpo = numero.replace('@s.whatsapp.net', '');
                    link.href = `/conversar?numero=${numeroLimpo}`;
                    link.innerHTML = "<i class='bi bi-whatsapp'></i> " + numeroLimpo;
                    link.className = "hover:underline text-green-600 text-xs font-semibold block";
                    link.target = "_blank";
                    lista.appendChild(link);
                });
            }

            // Resetar visibilidade do dropdown
            lista.classList.add('hidden');
            document.getElementById('setaToggle').classList.remove('bi-chevron-up');
            document.getElementById('setaToggle').classList.add('bi-chevron-down');

            // Mostrar ou esconder o botão "Parar Disparo" de acordo com o status
                const btnParar = document.getElementById('btnPararDisparo');
                if (data.status === 'rodando') {
                    btnParar.classList.remove('hidden');
                } else {
                    btnParar.classList.add('hidden');
                }


            // Exibir o modal
            const modal = document.getElementById('modalDisparo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}



function fecharModalDisparo() {
    const modal = document.getElementById('modalDisparo');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

function pararDisparo() {
    const id = document.getElementById('idDisparoSelecionado').value;
    
    if (!confirm('Tem certeza que deseja parar este disparo?')) return;

    fetch(`/disparo/${id}/cancelar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.mensagem);
        fecharModalDisparo();
        location.reload(); // ou só atualiza os cards via JS
    })
    .catch(() => alert('Erro ao cancelar o disparo.'));
}
</script>

@endsection
