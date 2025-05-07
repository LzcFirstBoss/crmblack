<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente\Cliente;
use App\Models\Bot\Bot;
use Carbon\Carbon;
use App\Models\Calendario\Evento;

class DashboardController extends Controller
{
    public function index()
    {
        // Contar Leads Captados Hoje
        $leadsHoje = Cliente::whereDate('created_at', Carbon::today())->count();

        // Contar todos os clientes (leads total)
        $leadsTotal = Cliente::count();

        // Bot ativo (verificar se tem algum ativo)
        $botAtivo = Bot::where('ativo', true)->exists();

        // Agendamentos (por enquanto manual)
        $agendamentos = Evento::whereDate('inicio', Carbon::today())->count();

        return view('user.index', compact('leadsHoje', 'leadsTotal', 'botAtivo', 'agendamentos'));
    }
}
