<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Facades\DB;
use App\Models\Kanban\Status;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\Http;



class KanbanController extends Controller
{

    public function index()
    {
        $sub = Mensagem::select(DB::raw('MAX(id) as id'))
            ->groupBy('numero_cliente');

        $ultimasMensagens = Mensagem::whereIn('id', $sub->pluck('id'))->get();

        $colunas = Status::orderBy('id')->get();
        $statusValidos = $colunas->pluck('id')->toArray();

        $clientes = Cliente::all()->keyBy(function ($c) {
            return str_replace('@s.whatsapp.net', '', $c->telefoneWhatsapp);
        });

        $mensagens = $ultimasMensagens->map(function ($mensagem) use ($clientes, $statusValidos) {
            $numero = $mensagem->numero_cliente;
            $cliente = $clientes[$numero] ?? null;

            if (!$cliente || !in_array($cliente->status_id, $statusValidos)) {
                return null;
            }

            $mensagem->cliente = $cliente;
            $mensagem->status_id = $cliente->status_id;
            return $mensagem;
        })
        ->filter()
        ->sortByDesc('data_e_hora_envio')
        ->groupBy(fn($msg) => $msg->cliente->status_id);

        return view('kanban.index', compact('mensagens', 'colunas'));
    }


    

    public function atualizarStatus(Request $request)
    {
        $mensagem = Mensagem::find($request->id);

        if ($mensagem) {
            $numero = $mensagem->numero_cliente;
            $cliente = Cliente::where('telefoneWhatsapp', $numero . '@s.whatsapp.net')->first();

            if ($cliente) {
                $cliente->status_id = $request->status;
                $cliente->save();
            }

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['erro' => 'Mensagem não encontrada'], 404);
    }

    public function parcial()
    {
        $sub = Mensagem::select(DB::raw('MAX(id) as id'))
            ->groupBy('numero_cliente');

        $ultimasMensagens = Mensagem::whereIn('id', $sub->pluck('id'))->get();

        $statusValidos = Status::pluck('id')->toArray();

        // → Aqui criamos um map de clientes para acesso rápido
        $clientes = Cliente::all()->keyBy(function ($c) {
            return str_replace('@s.whatsapp.net', '', $c->telefoneWhatsapp);
        });

        $mensagens = $ultimasMensagens->map(function ($mensagem) use ($statusValidos, $clientes) {
            $numero = $mensagem->numero_cliente;
            $cliente = $clientes[$numero] ?? null;

            if (!$cliente || !in_array($cliente->status_id, $statusValidos)) {
                return null;
            }

            $mensagem->status_id = $cliente->status_id;
            $mensagem->cliente = $cliente; // ← adiciona o cliente como objeto para acessar direto no Blade
            return $mensagem;
        })
        ->filter()
        ->sortByDesc('data_e_hora_envio')
        ->groupBy('status_id');

        $colunas = Status::orderBy('id')->get();

        return view('kanban._parcial', compact('mensagens', 'colunas'));
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
