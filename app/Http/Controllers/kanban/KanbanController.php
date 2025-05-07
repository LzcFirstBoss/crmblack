<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Facades\DB;
use App\Models\Kanban\Status;
use Illuminate\Support\Facades\Http;



class KanbanController extends Controller
{

    public function index()
    {
        $sub = Mensagem::select(DB::raw('MAX(id) as id'))
            ->groupBy('numero_cliente'); // agora pegando a última mensagem geral (não só do cliente)
    
        $mensagens = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('data_e_hora_envio', 'desc')
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
            ->orderBy('data_e_hora_envio', 'desc')
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

    public function historico($numero)
    {
        // Busca a foto de perfil pela API da Evolution
        $apiKey = env('EVOLUTION_API_KEY');
        $instance = env('EVOLUTION_INSTANCE_ID');
        $server = env('EVOLUTION_API_URL');

        $fotoPerfil = null;
        try {
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $apiKey
            ])->post("{$server}/chat/fetchProfilePictureUrl/{$instance}", [
                'number' => $numero
            ]);

            $json = $res->json();
            $fotoPerfil = $json['profilePictureUrl'] ?? null;
        } catch (\Exception $e) {
            $fotoPerfil = null; // fallback se der erro
        }

        return view('kanban.historico', compact('numero', 'fotoPerfil'));
    }
    
    public function atualizarHistorico($numero)
    {
        $mensagens = Mensagem::where('numero_cliente', $numero)
        ->with('usuario') // ← carrega o usuário vinculado à mensagem
        ->orderBy('data_e_hora_envio', 'asc')
        ->get();
    
        return view('kanban._mensagens', compact('mensagens'));
    }
}
