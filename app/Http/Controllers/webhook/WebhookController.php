<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Webhook\Mensagem;
use App\Models\Cliente\Cliente;

class WebhookController extends Controller
{
    public function receberMensagem(Request $request)
    {
        if ($request->header('apikey') !== config('app.APIKEY_SECRET')) {
            return response()->json(['erro' => 'Não autorizado'], 401);
        }

        $dados = is_array($request->all()) ? $request->all() : [$request->all()];

        foreach ($dados as $mensagem) {
            if (!$this->tipoEhValido($mensagem['tipo_de_mensagem'] ?? '')) {
                continue;
            }

            $tipo = $this->tipoNormalizado($mensagem['tipo_de_mensagem']);
            $numeroCliente = $mensagem['numero_cliente'] . '@s.whatsapp.net';
            $caminhoArquivo = null;

            if (in_array($tipo, ['imagem', 'audio', 'video']) && !empty($mensagem['base64'])) {
                $caminhoArquivo = $this->salvarArquivoBase64($mensagem['base64'], $tipo, $mensagem['numero_cliente']);
            }

            // Incrementar mensagens novas e criar cliente se não existir
            if (isset($mensagem['numero_cliente']) && !$this->foiEnviadoPorMimOuBot($mensagem)) {
                $numeroClienteSemDominio = $mensagem['numero_cliente'];
                $numeroCliente = $numeroClienteSemDominio . '@s.whatsapp.net';
            
                // Verificar se já existe cliente
                $cliente = Cliente::where('telefoneWhatsapp', $numeroCliente)->first();
            
                if (!$cliente) {
                    // Se não existe, cria com botativo e nome
                    $cliente = Cliente::create([
                        'telefoneWhatsapp'    => $numeroCliente,
                        'nome'                => $mensagem['nome'] ?? null,
                        'botativo'            => filter_var($mensagem['botativo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'qtd_mensagens_novas' => 1,
                    ]);
                } else {
                    // Se já existe, só incrementa as mensagens novas
                    $cliente->increment('qtd_mensagens_novas');
                }
            }

            // Salvar no banco
            $novaMensagem = Mensagem::create([
                'numero_cliente'    => $mensagem['numero_cliente'] ?? '',
                'tipo_de_mensagem'  => $mensagem['tipo_de_mensagem'] ?? '',
                'mensagem_enviada'  => $caminhoArquivo ?? ($mensagem['mensagem_enviada'] ?? null),
                'data_e_hora_envio' => $mensagem['data_e_hora_envio'] ?? now(),
                'enviado_por_mim'   => filter_var($mensagem['enviado_por_mim'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'usuario_id'        => $mensagem['usuario_id'] ?? null,
                'bot'               => filter_var($mensagem['bot'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);

            // Disparar evento WebSocket para navegador
            $this->notificarWebSocket($novaMensagem);
        }

        return response()->json(['status' => 'Mensagem(ns) salva(s) com sucesso']);
    }

    private function notificarWebSocket(Mensagem $mensagem)
    {
        try {
            Http::post('http://localhost:3001/enviar', [
                'evento' => 'novaMensagem',
                'dados' => [
                    'numero' => $mensagem->numero_cliente,
                    'mensagem' => $mensagem->mensagem_enviada,
                    'data_e_hora_envio' => $mensagem->data_e_hora_envio,
                    'enviado_por_mim' => $mensagem->enviado_por_mim,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar WebSocket: ' . $e->getMessage());
        }
    }

    private function tipoEhValido(string $tipo): bool
    {
        return in_array($tipo, [
            'conversation', 'extendedTextMessage', 'imageMessage', 'audioMessage', 'videoMessage',
        ]);
    }

    private function tipoNormalizado(string $tipoOriginal): string
    {
        return match ($tipoOriginal) {
            'conversation', 'extendedTextMessage' => 'texto',
            'imageMessage' => 'imagem',
            'audioMessage' => 'audio',
            'videoMessage' => 'video',
            default => 'desconhecido',
        };
    }

    private function foiEnviadoPorMimOuBot(array $mensagem): bool
    {
        return 
            filter_var($mensagem['enviado_por_mim'] ?? false, FILTER_VALIDATE_BOOLEAN) ||
            filter_var($mensagem['bot'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function salvarArquivoBase64(string $base64, string $tipo, string $numeroCliente): ?string
    {
        $extensao = match ($tipo) {
            'imagem' => 'jpg',
            'audio' => 'mp3',
            'video' => 'mp4',
            default => 'bin',
        };

        $pastaTipo = match ($tipo) {
            'imagem' => 'imagens',
            'audio' => 'audios',
            'video' => 'videos',
            default => 'outros',
        };

        $caminhoCompleto = "uploads/$numeroCliente/$pastaTipo";
        $nomeArquivo = uniqid() . '.' . $extensao;

        Storage::disk('public')->put("$caminhoCompleto/$nomeArquivo", base64_decode($base64));

        return "$caminhoCompleto/$nomeArquivo";
    }
}
