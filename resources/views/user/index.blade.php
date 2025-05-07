@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-10">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

        <!-- Leads Captados Hoje -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow hover:shadow-xl transition">
            <div class="flex items-center mb-5 space-x-4">
                <div class="bg-gray-100 rounded-full p-4 shadow-inner">
                    <i class="bi bi-person-plus-fill text-gray-800 text-3xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 uppercase tracking-widest">Hoje</span>
            </div>
            <h5 class="text-md text-gray-600">Leads Captados</h5>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ $leadsHoje }}</p>
        </div>

        <!-- Leads Total -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow hover:shadow-xl transition">
            <div class="flex items-center mb-5 space-x-4">
                <div class="bg-gray-100 rounded-full p-4 shadow-inner">
                    <i class="bi bi-people-fill text-gray-800 text-3xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 uppercase tracking-widest">Total</span>
            </div>
            <h5 class="text-md text-gray-600">Leads</h5>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ $leadsTotal }}</p>
        </div>

        <!-- Bot Ativo -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow hover:shadow-xl transition">
            <div class="flex items-center mb-5 space-x-4">
                <div class="bg-gray-100 rounded-full p-4 shadow-inner">
                    <i class="bi bi-robot text-orange-500 text-3xl"></i>
                </div>
                <span class="text-xs font-medium text-orange-500 uppercase tracking-widest">Ativo</span>
            </div>
            <h5 class="text-md text-gray-600">Bot Status</h5>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ $botAtivo ? 'Sim' : 'Não' }}</p>
        </div>

        <!-- Agendamentos -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow hover:shadow-xl transition">
            <div class="flex items-center mb-5 space-x-4">
                <div class="bg-gray-100 rounded-full p-4 shadow-inner">
                    <i class="bi bi-calendar-check-fill text-gray-800 text-3xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 uppercase tracking-widest">Hoje</span>
            </div>
            <h5 class="text-md text-gray-600">Agendamentos</h5>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ $agendamentos }}</p>
        </div>

    </div>
</div>
@endsection
