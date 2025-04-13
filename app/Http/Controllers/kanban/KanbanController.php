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
            ->where('enviado_por_mim', false) // só mensagens de clientes
            ->groupBy('numero_cliente');
    
        $mensagens = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status_id');
    
        $colunas = Status::orderBy('id')->get();
    
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
            ->where('enviado_por_mim', false) // só mensagens de clientes
            ->groupBy('numero_cliente');
    
        $mensagens = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status_id');
    
        $colunas = Status::orderBy('id')->get();
    
        return view('kanban._parcial', compact('mensagens', 'colunas'));
    }

    public function atualizarCor(Request $request, $id)
    {
        $status = Status::findOrFail($id);
        $status->cor = $request->cor;
        $status->save();

        return response()->json(['status' => 'cor atualizada']);
    }
}
