@extends('layouts.dashboard')

@section('title', 'Leads')

@section('content')
<div class="p-6 min-h-screen bg-gray-100">
    <h1 class="text-3xl font-bold mb-2">Leads</h1>
    <p class="mb-6 text-gray-600">{{ $clientes->count() }} leads encontrados</p>

    <!-- Filtros com sombra -->
<div class="bg-white shadow-sm rounded-lg p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <!-- Filtros -->
    <div class="flex flex-1 flex-col md:flex-row gap-4 w-full">
        <form method="GET" action="{{ route('leads.index') }}" class="flex flex-1 flex-col md:flex-row gap-4 w-full">

    <input type="text"
        name="busca"
        value="{{ request('busca') }}"
        placeholder="Buscar por nome, telefone, email..."
        class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />

    </div>
</form>

    <!-- Botão adicionar -->
    <a href="{{ route('leads.create') }}"
       class="bg-blue-600 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-blue-700 transition whitespace-nowrap">
        + Adicionar Lead
    </a>
</div>


    <!-- Listagem de Leads -->
    @foreach($clientes as $cliente)
   <a href="{{ route('leads.show', $cliente->id) }}" class="block transition hover:shadow-md hover:bg-gray-50 rounded-lg">

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex justify-between items-center">
                <div class="flex gap-4 items-center">
                    <div class="bg-green-500 text-white rounded-full h-10 w-10 flex items-center justify-center font-bold">
                        {{ strtoupper(substr($cliente->nome, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold">{{ $cliente->nome }}</h2>
                        <div class="text-sm text-gray-600 flex flex-wrap gap-2">
                            <span><i class="bi bi-telephone"></i> {{ preg_replace('/@.*/', '', $cliente->telefoneWhatsapp) }}</span>
                            @if($cliente->email)
                                <span><i class="bi bi-envelope"></i> {{ $cliente->email }}</span>
                            @endif
                        </div>
                        @if($cliente->empresa)
                            <div class="text-sm text-gray-500"><i class="bi bi-building"></i> {{ $cliente->empresa }}</div>
                        @endif
                    </div>
                </div>

                <!-- Status -->
                <div>
                    @if($cliente->status === 'Ganho')
                        <span class="bg-green-100 text-green-700 text-sm px-3 py-1 rounded-full font-semibold">Ganho</span>
                    @elseif($cliente->status === 'Novo')
                        <span class="bg-purple-100 text-purple-700 text-sm px-3 py-1 rounded-full font-semibold">Novo</span>
                    @else
                        <span class="bg-yellow-100 text-yellow-700 text-sm px-3 py-1 rounded-full font-semibold">Contactado</span>
                    @endif
                </div>
            </div>

<div class="text-sm text-gray-500 mt-3 flex justify-between">
    <span>Criado em: {{ \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y') }}</span>

    @if($cliente->ultima_mensagem_cliente)
        <span>Último contato: {{ \Carbon\Carbon::parse($cliente->ultima_mensagem_cliente)->format('d/m/Y, H:i') }}</span>
    @else
        <span>Último contato: -</span>
    @endif
</div>

        </div>
    </a>
    @endforeach
    <!-- Paginação -->
<div class="mt-8 text-center">
    <div class="text-sm text-gray-600 mb-2">
        Mostrando {{ $clientes->firstItem() }} a {{ $clientes->lastItem() }} de {{ $clientes->total() }} resultados
    </div>
    <div class="inline-block">
        {{ $clientes->links('vendor.pagination.tailwind') }}
    </div>
</div>


</div>

@endsection
