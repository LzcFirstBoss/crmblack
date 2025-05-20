<?php

namespace App\Http\Controllers\notificacao;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacao\Notificacao;

class NotificacaoController extends Controller
{
    public function listar()
    {
        $notificacoes = Notificacao::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->where('lida', 'FALSE')
            ->take(20)
            ->get();

        return response()->json($notificacoes);
    }

    public function marcarTodasComoLidas()
    {
        Notificacao::where('user_id', auth()->id())
            ->where('lida', false)
            ->update(['lida' => true]);

        return response()->json(['status' => 'ok']);
    }
    
    public function deletar($id)
    {
        $notificacao = Notificacao::where('user_id', auth()->id())->findOrFail($id);
        $notificacao->delete();

        return response()->json(['ok' => true]);
    }
}
