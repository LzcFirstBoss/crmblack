<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Str;

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
            'text'   => $mensagem
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

        // Salva no banco se a resposta foi 200 (OK)
        Mensagem::create([
            'numero_cliente' => $numero,
            'tipo_de_mensagem' => 'conversation',
            'mensagem_enviada' => $mensagem,
            'data_e_hora_envio' => now(),
            'enviado_por_mim' => true,
            'usuario_id' => auth()->id(), // se quiser associar com usuário logado, usar: auth()->id()
            'bot' => false,
        ]);

        return response()->json([
            'status' => 'Mensagem enviada com sucesso',
            'retorno' => $resposta
        ]);
    }
}
