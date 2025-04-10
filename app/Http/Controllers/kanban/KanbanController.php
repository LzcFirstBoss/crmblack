<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Facades\DB;
use App\Models\Kanban\Status;

class KanbanController extends Controller
{

    public function index()
    {
        $sub = Mensagem::select(DB::raw('MAX(id) as id'))
            ->groupBy('numero_cliente');
    
        $mensagens = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status_id'); // agora usamos o status_id
    
        $colunas = Status::orderBy('id')->get(); // pegamos os status do banco
    
        return view('kanban.index', compact('mensagens', 'colunas'));
    }

    

    public function atualizarStatus(Request $request)
    {
        $mensagem = Mensagem::find($request->id);
    
        if ($mensagem) {
            $mensagem->status_id = $request->status; // ← ATENÇÃO AQUI
            $mensagem->save();
    
            return response()->json(['status' => 'ok']);
        }
    
        return response()->json(['erro' => 'Mensagem não encontrada'], 404);
    }

    public function parcial()
    {
        $sub = Mensagem::select(DB::raw('MAX(id) as id'))
            ->groupBy('numero_cliente');
    
        $mensagens = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status_id');
    
        $colunas = \App\Models\Kanban\Status::orderBy('id')->get();
    
        return view('kanban._parcial', compact('mensagens', 'colunas'));
    }
}
