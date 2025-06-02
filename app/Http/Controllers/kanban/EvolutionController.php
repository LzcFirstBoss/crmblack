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

            $numeroLimpo = preg_replace('/\D/', '', $numeroRecebido);
            $numeroEvolution = $numeroLimpo . '@s.whatsapp.net';

            if (empty($numeroLimpo) || empty($mensagem)) {
                return response()->json(['erro' => 'Número ou mensagem inválida.'], 400);
            }

            // Iniciar payload com texto
            $payload = [
                'number' => $numeroEvolution,
                'text'   => $mensagem,
            ];

            if ($request->has('resposta')) {
                $resposta = $request->input('resposta');

                // quoted para API
                if (isset($resposta['id'], $resposta['texto'], $resposta['numero'])) {
                    $payload['quoted'] = [
                        'key' => [
                            'remoteJid' => $resposta['numero'] . '@s.whatsapp.net',
                            'fromMe' => $resposta['fromMe'] ?? false,
                            'id' => $resposta['id']
                        ],
                        'message' => [
                            'conversation' => $resposta['texto']
                        ]
                    ];
                }
            }
            
            // Salvar no banco
            $mensagemSalva = Mensagem::create([
                'numero_cliente'          => $numeroLimpo,
                'tipo_de_mensagem'        => 'conversation',
                'mensagem_enviada'        => $mensagem,
                'data_e_hora_envio'       => now(),
                'enviado_por_mim'         => true,
                'usuario_id'              => auth()->id(),
                'bot'                     => false,
                'status'                  => 'enviando',
                'mensagem_respondida_id'  => $resposta['id'] ?? null,
            ]);

            // Enviar para Evolution
            $instance = env('EVOLUTION_INSTANCE_ID');
            $apiKey   = env('EVOLUTION_API_KEY');
            $server   = env('EVOLUTION_API_URL');

            $url = "{$server}/message/sendText/{$instance}";

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
                $mensagemSalva->update(['status' => 'erro']);

                return response()->json(['erro' => 'Erro ao conectar na API Evolution: ' . $erroCurl], 500);
            }


            $resposta = json_decode($response, true);

            if (isset($resposta['status']) && $resposta['status'] == 400) {
                $mensagemSalva->update(['status' => 'erro']);

                return response()->json([
                    'erro' => 'Erro da Evolution: ' . json_encode($resposta['response']['message'][0][0] ?? 'Erro desconhecido')
                ], 400);
            }

            $atualizacao = ['status' => 'enviado'];
            if (isset($resposta['key']['id'])) {
                $atualizacao['id_mensagem'] = $resposta['key']['id'];
            }

            $mensagemSalva->update($atualizacao);

            return response()->json([
                'status' => 'Mensagem enviada com sucesso',
                'id_mensagem' => $resposta['key']['id'] ?? null,
                'retorno' => $resposta
            ]);
        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno: ' . $e->getMessage()], 500);
        }
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

        // Caminho de destino -> public/uploads/{numero}/audios
        $tipo = 'audios';
        $diretorio = "public/uploads/{$numero}/{$tipo}";

        // Decodificar base64
        $audioBinario = base64_decode($audioBase64);

        // Nome do arquivo (único)
        $nomeArquivo = uniqid() . '.webm';

        // Salvar o arquivo
        $caminhoSalvo = Storage::put("{$diretorio}/{$nomeArquivo}", $audioBinario);

        // Ajustar o caminho para salvar no banco (sem "public/")
        $caminhoParaBanco = str_replace("public/", "", "{$diretorio}/{$nomeArquivo}");

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
            'mensagem_enviada' => $caminhoParaBanco, // agora só o caminho relativo correto
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

    public function enviarMidia(Request $request)
    {
        $EVOLUTION_INSTANCE_ID = env('EVOLUTION_INSTANCE_ID');
        $EVOLUTION_API_URL = env('EVOLUTION_API_URL');
        $EVOLUTION_API_KEY = env('EVOLUTION_API_KEY');

        $request->validate([
            'numero' => 'required',
            'arquivos' => 'required|array|max:5',
            'arquivos.*' => 'required|file|max:10240', // 10MB por arquivo
            'mediatype' => 'required|in:image,video,document,audio',
            'caption' => 'nullable|string'
        ]);

        $numero = preg_replace('/\D/', '', $request->numero);
        $numeroEvolution = $numero . '@s.whatsapp.net';

        $resultados = [];

        foreach ($request->file('arquivos') as $arquivo) {
            try {
                // === Preparar dados ===
                $base64 = base64_encode(file_get_contents($arquivo));
                $extensao = $arquivo->getClientOriginalExtension();
                $fileName = time() . '_' . uniqid() . '.' . $extensao;

                $tipoDiretorio = match ($request->mediatype) {
                    'image' => 'images',
                    'video' => 'videos',
                    'audio' => 'audios',
                    'document' => 'documents',
                    default => 'others',
                };

                $path = "uploads/{$numero}/{$tipoDiretorio}";
                $caminhoSalvo = $arquivo->storeAs("public/{$path}", $fileName);
                $caminhoParaBanco = str_replace('public/', '', $caminhoSalvo);

                // === Salvar no banco com status "enviando" ===
                $mensagem = Mensagem::create([
                    'numero_cliente' => $numero,
                    'tipo_de_mensagem' => $request->mediatype,
                    'mensagem_enviada' => $caminhoParaBanco,
                    'data_e_hora_envio' => now(),
                    'enviado_por_mim' => true,
                    'usuario_id' => auth()->id(),
                    'bot' => false,
                    'status' => 'enviando'
                ]);

                // === Enviar para Evolution ===
                $payload = [
                    'number' => $numeroEvolution,
                    'options' => [
                        'delay' => 100,
                        'presence' => 'composing',
                    ],
                    'mediatype' => $request->mediatype,
                    'caption' => $request->caption ?? '',
                    'media' => $base64,
                    'fileName' => $fileName
                ];

                $url = "{$EVOLUTION_API_URL}/message/sendMedia/{$EVOLUTION_INSTANCE_ID}";

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "apikey: {$EVOLUTION_API_KEY}",
                    ],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                $response = curl_exec($curl);
                $erroCurl = curl_error($curl);
                curl_close($curl);

                // === Tratamento de erro cURL ===
                if ($erroCurl) {
                    $mensagem->update(['status' => 'erro']);
                    $resultados[] = [
                        'arquivo' => $fileName,
                        'status' => 'erro',
                        'mensagem' => 'Erro cURL: ' . $erroCurl
                    ];
                    continue;
                }

                // === Tratamento para resposta vazia ou inválida ===
                if (!$response || empty(trim($response))) {
                    $mensagem->update(['status' => 'erro']);
                    $resultados[] = [
                        'arquivo' => $fileName,
                        'status' => 'erro',
                        'mensagem' => 'Sem resposta da API da Evolution (timeout ou falha).'
                    ];
                    continue;
                }

                $resposta = json_decode($response, true);

                // === Tratamento para erro 400 da API ===
                if (isset($resposta['status']) && $resposta['status'] == 400) {
                    $mensagem->update(['status' => 'erro']);
                    $mensagemErro = $resposta['response']['message'][0][0] ?? 'Erro desconhecido';
                    $resultados[] = [
                        'arquivo' => $fileName,
                        'status' => 'erro',
                        'mensagem' => json_encode($mensagemErro)
                    ];
                    continue;
                }

                // === Atualiza status no banco ===
                $atualizacao = ['status' => 'enviado'];
                if (isset($resposta['key']['id'])) {
                    $atualizacao['id_mensagem'] = $resposta['key']['id'];
                }

                $mensagem->update($atualizacao);

                $resultados[] = [
                    'arquivo' => $fileName,
                    'status' => 'enviado',
                    'id_mensagem' => $resposta['key']['id'] ?? null
                ];
            } catch (\Throwable $e) {
                $resultados[] = [
                    'arquivo' => $arquivo->getClientOriginalName(),
                    'status' => 'erro',
                    'mensagem' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'status' => 'finalizado',
            'resultados' => $resultados
        ]);
    }

    public function apagarMensagemParaTodos(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string',
            'remoteJid' => 'required|string',
            'fromMe' => 'required|boolean',
        ]);

        $server = env('EVOLUTION_API_URL');
        $instance = env('EVOLUTION_INSTANCE_ID');
        $apikey = env('EVOLUTION_API_KEY');

        $url = "{$server}/chat/deleteMessageForEveryone/{$instance}";

        $payload = [
            'id' => $validated['id'],
            'remoteJid' => $validated['remoteJid'],
            'fromMe' => $validated['fromMe'],
        ];

        if (!empty($validated['participant'])) {
            $payload['participant'] = $validated['participant'];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => $apikey,
        ])->withBody(json_encode($payload), 'application/json')
        ->delete($url);

        if ($response->successful()) {
            Mensagem::where('id_mensagem', $validated['id'])->update(['status' => 'apagado']);
            return response()->json(['sucesso' => true, 'resposta' => $response->json()]);
        }

        return response()->json(['erro' => 'Falha ao deletar', 'detalhes' => $response->body()], $response->status());
    }

    public function editarMensagem(Request $request)
    {
        $request->validate([
            'number' => 'required|numeric', // Ex: 556499999999
            'text' => 'required|string',
            'remoteJid' => 'required|string', // Ex: 556499999999@s.whatsapp.net
            'fromMe' => 'required|boolean',
            'id' => 'required|string', // ID da mensagem
        ]);

        $payload = [
            'number' => $request->number,
            'text' => $request->text,
            'key' => [
                'remoteJid' => $request->remoteJid,
                'fromMe' => $request->fromMe,
                'id' => $request->id,
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => env('EVOLUTION_API_KEY'),
        ])->post(env('EVOLUTION_API_URL') . '/chat/updateMessage/' . env('EVOLUTION_INSTANCE_ID'), $payload);

        if ($response->successful()) {
            Mensagem::where('id_mensagem', $request->id)->update([
                'mensagem_enviada' => $request->text,
                'status' => 'editado'
            ]);

            return response()->json(['sucesso' => true, 'retorno_api' => $response->json()]);
        }

        return response()->json(['erro' => true, 'mensagem' => $response->body()], $response->status());
    }
}