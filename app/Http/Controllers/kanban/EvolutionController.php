<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bot\Bot;
use App\Models\Bot\FuncoesBot;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;


class EvolutionController extends Controller
{    
    public function conectarInstancia()
    {
        $apiUrl = config('services.evolution.url');
        $apiKey = config('services.evolution.key');
        $instancia  = config('services.evolution.id');
    
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
        $userId = auth()->id();
    
        $estadoAtual = 'DISCONNECTED';
    
        $resposta = Http::withHeaders([
            'apikey' => $apiKey
        ])->get("{$apiUrl}/instance/connectionState/{$instanciaId}");
    
        if ($resposta->successful() && ($resposta['instance']['state'] ?? '') === 'open') {
            $estadoAtual = 'CONNECTED';
        }
    
        // Atualiza o status no banco
        DB::table('evolutions')->where('instancia_id', $instanciaId)->update([
            'status_conexao' => $estadoAtual,
            'updated_at' => now()
        ]);
    
        // Pega dados atualizados da instância
        $instancia = DB::table('evolutions')->where('instancia_id', $instanciaId)->first();
    
        $bots = Bot::where('lixeira', false)->get();
        $funcoes = FuncoesBot::all();
    
        // Manda tudo pra view
        return view('evolution.config', [
            'instancia' => $instancia,
            'bots' => $bots,
            'funcoes' => $funcoes
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

    public function enviarMensagem(Request $request)
    {
        try {
            $numeroRecebido = $request->input('numero');
            $mensagem = trim($request->input('mensagem'));
    
            // Limpar o número para uso interno (banco de dados)
            $numeroLimpo = preg_replace('/\D/', '', $numeroRecebido); // Ex: 556499572510
    
            // Número formatado para enviar para Evolution
            $numeroEvolution = $numeroLimpo . '@s.whatsapp.net';
    
            if (empty($numeroLimpo) || empty($mensagem)) {
                return response()->json(['erro' => 'Número ou mensagem inválida.'], 400);
            }
    
            // Dados da API Evolution
            $instance = env('EVOLUTION_INSTANCE_ID');
            $apiKey   = env('EVOLUTION_API_KEY');
            $server   = env('EVOLUTION_API_URL');
    
            $url = "{$server}/message/sendText/{$instance}";
    
            $payload = [
                'number' => $numeroEvolution, // Aqui manda com @s.whatsapp.net para Evolution
                'text'   => $mensagem
            ];
    
            // Envio para Evolution
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
            $erroCurl = curl_error($curl);
            curl_close($curl);
    
            if ($erroCurl) {
                return response()->json(['erro' => 'Erro ao conectar na API Evolution: ' . $erroCurl], 500);
            }
    
            $resposta = json_decode($response, true);
    
            if (isset($resposta['status']) && $resposta['status'] == 400) {
                return response()->json([
                    'erro' => 'Erro da Evolution: ' . json_encode($resposta['response']['message'][0][0] ?? 'Erro desconhecido')
                ], 400);
            }
    
            // Agora salvando o número LIMPO no banco
            Mensagem::create([
                'numero_cliente' => $numeroLimpo, // <- só o número, sem @s.whatsapp.net
                'tipo_de_mensagem' => 'conversation',
                'mensagem_enviada' => $mensagem,
                'data_e_hora_envio' => now(),
                'enviado_por_mim' => true,
                'usuario_id' => auth()->id(),
                'bot' => false,
            ]);
    
            return response()->json([
                'status' => 'Mensagem enviada com sucesso',
                'retorno' => $resposta
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

public function audioForm()
{
    return view('teste.form');
}

public function audioEnviar(Request $request)
{
    // Garantir número no formato correto
    $numero = preg_replace('/\D/', '', $request->input('numero'));
    $audioBase64 = $request->input('audio_base64');

    // Verificar se está ok
    if (strlen($numero) < 10 || !$audioBase64) {
        return response()->json(['erro' => 'Número ou áudio inválido.']);
    }

    // ====== SALVAR O ÁUDIO NO STORAGE ======

    // Caminho de destino -> public/uploads/{numero}/audio
    $tipo = 'audios';
    $diretorio = "public/uploads/{$numero}/{$tipo}";

    // Decodificar base64
    $audioBinario = base64_decode($audioBase64);

    // Nome do arquivo (único)
    $nomeArquivo = uniqid() . '.webm';

    // Salvar o arquivo
    Storage::put("{$diretorio}/{$nomeArquivo}", $audioBinario);

    // Gerar URL pública (caso precise em outras telas, mas aqui vamos salvar o caminho relativo)
    $caminhoArquivo = "{$diretorio}/{$nomeArquivo}";

    // ====== ENVIAR PARA EVOLUTION ======

    $instance = env('EVOLUTION_INSTANCE_ID');
    $apiKey = env('EVOLUTION_API_KEY');
    $server = env('EVOLUTION_API_URL');

    $url = "{$server}/message/sendWhatsAppAudio/{$instance}";

    $payload = [
        "number" => $numero,
        "audio" => $audioBase64,
        "delay" => 0,
        "encoding" => true
    ];

    // Enviar via cURL
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
    $erroCurl = curl_error($curl);
    curl_close($curl);

    if ($erroCurl) {
        return response()->json(['erro' => 'Erro cURL: ' . $erroCurl]);
    }

    $resposta = json_decode($response, true);

    if (isset($resposta['status']) && $resposta['status'] == 400) {
        return response()->json(['erro' => 'Erro da Evolution: ' . json_encode($resposta['response']['message'] ?? 'Erro desconhecido')]);
    }

    // ====== SALVAR NO BANCO ======

    Mensagem::create([
        'numero_cliente' => $numero,
        'tipo_de_mensagem' => 'audio',
        'mensagem_enviada' => $caminhoArquivo, // salva o caminho do áudio salvo
        'data_e_hora_envio' => now(),
        'enviado_por_mim' => true,
        'usuario_id' => auth()->id(),
        'bot' => false,
    ]);

    return response()->json([
        'status' => 'Áudio enviado com sucesso',
        'resposta' => $resposta
    ]);
}


}