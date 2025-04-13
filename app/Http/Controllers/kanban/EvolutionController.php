<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvolutionController extends Controller
{
    public function enviarMensagem(Request $request)
    {
        $numero = $request->input('numero');
        $mensagem = trim($request->input('mensagem'));
    
        if (!$numero || !$mensagem) {
            return response()->json(['erro' => 'Número ou mensagem inválida'], 400);
        }
    
        $instance = env('EVOLUTION_INSTANCE_ID');
        $apiKey   = env('EVOLUTION_API_KEY');
        $server   = env('EVOLUTION_API_URL');
    
        $url = "{$server}/message/sendText/{$instance}";
    
        $payload = [
            'number' => $numero,
            'text'   => $mensagem // <- corrigido aqui
        ];
    
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "apikey: {$apiKey}",
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
    
        $response = curl_exec($curl);
        $erro = curl_error($curl);
        curl_close($curl);
    
        if ($erro) {
            return response()->json(['erro' => 'Erro ao conectar com a API: ' . $erro], 500);
        }
    
        $resposta = json_decode($response, true);
    
        if (isset($resposta['status']) && $resposta['status'] === 400) {
            return response()->json([
                'erro' => 'Erro da Evolution: ' . json_encode($resposta['response']['message'][0][0] ?? 'Erro desconhecido')
            ], 400);
        }
    
        return response()->json([
            'status' => 'Mensagem enviada com sucesso',
            'retorno' => $resposta
        ]);
    }
    
}
