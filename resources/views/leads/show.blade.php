@extends('layouts.dashboard')

@section('title', 'Leads: ' . $cliente->nome)

@section('content')
<div class="p-6 min-h-screen bg-gray-50">
            <a href="{{ route('leads.index') }}" class="text-sm text-blue-600 hover:underline">
                <i class="bi bi-arrow-left-short"></i> Voltar para lista
            </a>    
    <!-- Topo: Nome e Ações -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div class="flex items-center gap-4">
            <div class="bg-green-500 text-white  rounded-full h-16 w-16 flex items-center justify-center text-2xl font-bold shadow">
                {{ strtoupper(substr($cliente->nome, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $cliente->nome }}</h1>
                <span class="mt-1 inline-flex items-center bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-medium gap-2">
                    <i class="bi bi-person-check-fill text-lg"></i>
                    {{ ucfirst($cliente->status ?? 'Indefinido') }}
                </span>
            </div>
        </div>

<div class="flex gap-2 flex-wrap">
    <!-- Editar -->
    <a href="{{ route('leads.edit', $cliente->id) }}" class="flex items-center bg-white gap-2 text-sm px-4 py-2 shadow-md rounded hover:bg-gray-100 transition">
        <i class="bi bi-pencil-square"></i> Editar
    </a>

    <!-- Excluir -->
    <button type="button"
        onclick="abrirModalExclusao()"
        class="flex items-center gap-2 text-sm px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
        <i class="bi bi-trash"></i> Excluir
    </button>

    <!-- Conversar -->
    <a href="{{ url('/conversar?numero=' . preg_replace('/@.*/', '', $cliente->telefoneWhatsapp)) }}"
        class="flex items-center bg-green-500 text-white gap-2 text-sm px-4 py-2 rounded shadow-md hover:bg-green-600 transition">
        <i class="bi bi-whatsapp"></i> Conversar
    </a>
</div>

    </div>

    <!-- Bloco: Informações de Contato -->
    <x-card title="Informações de Contato" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div class="flex gap-2 items-center">
                <i class="bi bi-telephone text-green-600"></i>
                <strong>Telefone:</strong> {{ preg_replace('/@.*/', '', $cliente->telefoneWhatsapp) }}
            </div>
            <div class="flex gap-2 items-center">
                <i class="bi bi-envelope text-blue-600"></i>
                <strong>Email:</strong> {{ $cliente->email ?? '-' }}
            </div>
            <div class="flex gap-2 items-center">
                <i class="bi bi-building text-purple-600"></i>
                <strong>Empresa:</strong> {{ $cliente->empresa ?? '-' }}
            </div>
        </div>
    </x-card>

    <!-- Bloco: Classificação -->
    <x-card title="Status e Classificação" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div class="flex gap-2 items-center">
                <span class="h-2 w-2 bg-blue-600 rounded-full"></span>
                <strong>Status:</strong> {{ ucfirst($cliente->status ?? '-') }}
            </div>
            <div class="flex gap-2 items-center">
                <i class="bi bi-diagram-3 text-purple-600"></i>
                <strong>Origem:</strong> {{ $cliente->origem ?? '-' }}
            </div>
        </div>
    </x-card>

    <!-- Bloco: Tags -->
    <x-card title="Tags" class="mb-6">
        @if($cliente->tags)
            @foreach(explode(',', $cliente->tags) as $tag)
                <span class="inline-flex items-center px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full mr-2 mb-2">
                    <i class="bi bi-tag mr-1"></i> {{ trim($tag) }}
                </span>
            @endforeach
        @else
            <p class="text-sm text-gray-500">Nenhuma tag registrada.</p>
        @endif
    </x-card>

    <!-- Bloco: Notas -->
    <x-card title="Notas" class="mb-6">
        <p class="text-sm text-gray-700">
            {{ $cliente->notas ?? 'Sem observações.' }}
        </p>
    </x-card>

    <!-- Bloco: Sistema -->
    <x-card title="Informações do Sistema">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
            <div>
                <i class="bi bi-calendar-event"></i>
                <strong class="block">Criado em:</strong>
                {{ \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y, H:i') }}
            </div>
            <div>
                <i class="bi bi-clock-history"></i>
                <strong class="block">Atualizado em:</strong>
                {{ \Carbon\Carbon::parse($cliente->updated_at)->format('d/m/Y, H:i') }}
            </div>
<div>
    <i class="bi bi-chat-dots-fill"></i>
    <strong class="block">Último contato:</strong>
    {{ $cliente->ultima_mensagem_cliente ? \Carbon\Carbon::parse($cliente->ultima_mensagem_cliente)->format('d/m/Y, H:i') : '-' }}
</div>

        </div>
    </x-card>
</div>

<!-- Modal de Exclusão -->
<div id="modalExclusao" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-xl font-bold mb-4 text-red-600">Confirmação de Exclusão</h2>
        <p class="mb-4 text-gray-700">
            Digite <strong>excluir</strong> abaixo para confirmar a exclusão do lead e todas as mensagens associadas.
        </p>

        <input id="confirmarTexto" type="text" placeholder="Digite 'excluir'" oninput="verificarTexto()"
            class="w-full border border-gray-300 rounded px-4 py-2 mb-4">

        <div class="flex justify-between">
            <button onclick="fecharModalExclusao()" type="button"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancelar</button>

            <form id="formExclusao" method="POST" action="{{ route('leads.destroy', $cliente->id) }}">
                @csrf
                @method('DELETE')
                <button id="btnConfirmarExclusao" type="submit" disabled
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">
                    Confirmar Exclusão
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalExclusao() {
        document.getElementById('modalExclusao').classList.remove('hidden');
    }

    function fecharModalExclusao() {
        document.getElementById('modalExclusao').classList.add('hidden');
        document.getElementById('confirmarTexto').value = '';
        document.getElementById('btnConfirmarExclusao').disabled = true;
    }

    function verificarTexto() {
        const input = document.getElementById('confirmarTexto').value.trim().toLowerCase();
        const botao = document.getElementById('btnConfirmarExclusao');
        botao.disabled = (input !== 'excluir');
    }
</script>

@endsection
