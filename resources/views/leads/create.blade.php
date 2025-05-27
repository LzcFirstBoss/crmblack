@extends('layouts.dashboard')

@section('title', 'Adicionar Novo Lead')

@section('content')

<div class="p-6 min-h-screen bg-gray-50">
    @if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul class="list-disc pl-5 text-sm">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('leads.store') }}" method="POST">
        @csrf

        <!-- Cabeçalho -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-person-plus"></i> Adicionar Novo Lead
            </h1>
            <a href="{{ route('leads.index') }}" class="text-sm text-blue-600 hover:underline">
                <i class="bi bi-arrow-left-short"></i> Voltar para lista
            </a>
        </div>

        <!-- Informações de Contato -->
        <x-card title='Informações de Contato <i class="bi bi-person-lines-fill"></i>' class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nome:</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Telefone (com DDD):</label>
                    <input type="text" name="telefoneWhatsapp" value="{{ old('telefoneWhatsapp') }}" required class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Email:</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </x-card>


        <!-- Botão -->
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                <i class="bi bi-check-circle"></i> Salvar Lead
            </button>
        </div>
    </form>
</div>
@endsection
