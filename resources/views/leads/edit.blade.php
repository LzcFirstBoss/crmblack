@extends('layouts.dashboard')

@section('title', 'Editar Lead: ' . $cliente->nome)

@section('content')
<div class="p-6 min-h-screen bg-gray-50">
    <form action="{{ route('leads.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar Lead</h1>
            <a href="{{ route('leads.show', $cliente->id) }}" class="text-sm text-blue-600 hover:underline">← Voltar</a>
        </div>

        <x-card title="Informações de Contato" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold">Nome:</label>
                    <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}" class="w-full border rounded px-4 py-2 mt-1">
                </div>
                <div>
                    <label class="text-sm font-semibold">Telefone:</label>
                    <input type="text" name="telefoneWhatsapp" value="{{ old('telefoneWhatsapp', preg_replace('/@.*/', '', $cliente->telefoneWhatsapp)) }}" class="w-full border rounded px-4 py-2 mt-1">
                </div>
                <div>
                    <label class="text-sm font-semibold">Email:</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="w-full border rounded px-4 py-2 mt-1">
                </div>
            </div>
        </x-card>

        <x-card title="Status e Classificação" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold">Origem:</label>
                    <input type="text" name="origem" value="{{ old('origem', $cliente->origem) }}" class="w-full border rounded px-4 py-2 mt-1">
                </div>
            </div>
        </x-card>

        <x-card title="Tags" class="mb-6">
            <input type="text" name="tags" value="{{ old('tags', $cliente->tags) }}" class="w-full border rounded px-4 py-2 mt-1" placeholder="Ex: Cliente VIP, Recomendado">
        </x-card>

        <x-card title="Notas" class="mb-6">
            <textarea name="notas" rows="4" class="w-full border rounded px-4 py-2 mt-1" placeholder="Notas sobre o lead...">{{ old('notas', $cliente->notas) }}</textarea>
        </x-card>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
