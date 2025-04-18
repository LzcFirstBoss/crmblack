<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;


class EvolutionController extends Controller
{    
    public function conectarInstancia()
    {
        $apiUrl = env('EVOLUTION_API_URL');
        $apiKey = env('EVOLUTION_API_KEY');
        $instancia = env('EVOLUTION_INSTANCE_ID');
    
        // 1. Conectar (gerar QR)
        $connect = Http::withHeaders([
            'apikey' => $apiKey
        ])->get("{$apiUrl}/instance/connect/{$instancia}");
    
        if (!$connect->successful()) {
            return response()->json(['erro' => true, 'mensagem' => 'Erro ao gerar QR Code.']);
        }
    
        return response()->json([
            'status' => 'AGUARDANDO',
            'base64' => $connect['base64'],
            'code' => $connect['code']
        ]);
    }
    
    
    public function verificarStatus()
    {
        $apiUrl = env('EVOLUTION_API_URL');
        $apiKey = env('EVOLUTION_API_KEY');
        $instancia = env('EVOLUTION_INSTANCE_ID');
    
        $resposta = Http::withHeaders([
            'apikey' => $apiKey
        ])->get("{$apiUrl}/instance/connectionState/{$instancia}");
    
        if (!$resposta->successful()) {
            return response()->json(['erro' => true, 'mensagem' => 'Erro ao consultar conexão.']);
        }
    
        $estado = $resposta['instance']['state'];
        $estadoAtual = $estado === 'open' ? 'CONNECTED' : 'DISCONNECTED';
        $botativo = $estado === 'open';
    
        DB::table('evolutions')->where('instancia_id', $instancia)->update([
            'status_conexao' => $estadoAtual,
            'botativo' => $botativo,
            'updated_at' => now()
        ]);
    
        return response()->json([
            'status' => $estadoAtual
        ]);
    }    
    
    
    public function painelWhatsapp()
    {
        $instanciaId = env('EVOLUTION_INSTANCE_ID');
        $apiUrl = env('EVOLUTION_API_URL');
        $apiKey = env('EVOLUTION_API_KEY');
    
        $estadoAtual = 'DISCONNECTED';
    
        $resposta = Http::withHeaders([
            'apikey' => $apiKey
        ])->get("{$apiUrl}/instance/connectionState/{$instanciaId}");
    
        if ($resposta->successful() && ($resposta['instance']['state'] ?? '') === 'open') {
            $estadoAtual = 'CONNECTED';
        }
    
        // Atualiza o banco apenas com status de conexão
        DB::table('evolutions')->where('instancia_id', $instanciaId)->update([
            'status_conexao' => $estadoAtual,
            'updated_at' => now()
        ]);
    
        // Recupera instância atualizada para exibir na view
        $instancia = DB::table('evolutions')->where('instancia_id', $instanciaId)->first();
    
        return view('evolution.config', [
            'instancia' => $instancia
        ]);
    }
    
    

    public function logout(Request $request)
    {
        $apiUrl = env('EVOLUTION_API_URL');
        $apiKey = env('EVOLUTION_API_KEY');
        $instancia = env('EVOLUTION_INSTANCE_ID');
    
        $resposta = Http::withHeaders([
            'apikey' => $apiKey
        ])->withOptions([
            'http_errors' => false
        ])->delete("{$apiUrl}/instance/logout/{$instancia}");
    
        $statusCode = $resposta->status();
        $body = $resposta->json();
    
        // Atualiza status no banco se for sucesso
        if ($resposta->ok() && ($body['status'] ?? '') === 'SUCCESS') {
            DB::table('evolutions')
                ->where('instancia_id', $instancia)
                ->update([
                    'status_conexao' => 'DISCONNECTED',
                    'updated_at' => now()
                ]);
        }
    
        return response()->json($body, $statusCode);
    }    
}
