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

            if (in_array($tipo, ['imagem', 'audio', 'video', 'documento']) && !empty($mensagem['base64'])) {
                $nomeArquivoOriginal = $mensagem['nome_arquivo'] ?? null;
                $caminhoArquivo = $this->salvarArquivoBase64(
                    $mensagem['base64'],
                    $tipo,
                    $mensagem['numero_cliente'],
                    $nomeArquivoOriginal
                );
            }

            // Incrementar mensagens novas e criar cliente se não existir
            if (isset($mensagem['numero_cliente']) && !$this->foiEnviadoPorMimOuBot($mensagem)) {
                $numeroClienteSemDominio = $mensagem['numero_cliente'];
                $numeroCliente = $numeroClienteSemDominio . '@s.whatsapp.net';
            
                // Verificar se já existe cliente
                $cliente = Cliente::where('telefoneWhatsapp', $numeroCliente)->first();
            
                if (!$cliente) {
                    // Se não existe, cria com botativo e nome e status id como 1 (Aguardando)
                    $cliente = Cliente::create([
                        'telefoneWhatsapp'    => $numeroCliente,
                        'nome'                => $mensagem['nome'] ?? null,
                        'botativo'            => filter_var($mensagem['botativo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'qtd_mensagens_novas' => 1,
                        'status_id' => 1,
                    ]);
                } else {
                    // Se já existe, só incrementa as mensagens novas
                    $cliente->increment('qtd_mensagens_novas');
                }
            }

            // Salvar no banco
            $novaMensagem = Mensagem::create([
                'numero_cliente'    => $mensagem['numero_cliente'] ?? '',
                'id_mensagem'       => $mensagem['id_mensagem'] ?? null,
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
            // Buscar o cliente correspondente
            $cliente = Cliente::where('telefoneWhatsapp', $mensagem->numero_cliente . '@s.whatsapp.net')->first();

            // Enviar via WebSocket
            Http::post('http://localhost:3001/enviar', [
                'evento' => 'kanban:novaMensagem',
                'dados' => [
                    'id' => $mensagem->id,
                    'numero' => $mensagem->numero_cliente,
                    'conteudo' => $mensagem->mensagem_enviada,
                    'enviado_por_mim' => $mensagem->enviado_por_mim,
                    'bot' => $mensagem->bot,
                    'usuario' => optional($mensagem->usuario)->name,
                    'status_id' => $cliente?->status_id,
                    'mensagens_novas' => $cliente?->qtd_mensagens_novas ?? 0, // ← BOLINHA DE NOTIFICAÇÃO
                    'created_at' => \Carbon\Carbon::parse($mensagem->data_e_hora_envio)->format('d/m H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar WebSocket: ' . $e->getMessage());
        }
    }

    private function tipoEhValido(string $tipo): bool
    {
        return in_array($tipo, [
            'conversation', 'extendedTextMessage', 'imageMessage',
            'audioMessage', 'videoMessage', 'documentMessage',
        ]);
    }

    private function tipoNormalizado(string $tipoOriginal): string
    {
        return match ($tipoOriginal) {
            'conversation', 'extendedTextMessage' => 'texto',
            'imageMessage' => 'imagem',
            'audioMessage' => 'audio',
            'videoMessage' => 'video',
            'documentMessage' => 'documento',
            default => 'desconhecido',
        };
    }

    private function foiEnviadoPorMimOuBot(array $mensagem): bool
    {
        return 
            filter_var($mensagem['enviado_por_mim'] ?? false, FILTER_VALIDATE_BOOLEAN) ||
            filter_var($mensagem['bot'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function salvarArquivoBase64(string $base64, string $tipo, string $numeroCliente, ?string $nomeOriginal = null): ?string
    {
        // Pega a extensão
        $extensao = $nomeOriginal
            ? pathinfo($nomeOriginal, PATHINFO_EXTENSION)
            : match ($tipo) {
                'imagem' => 'jpg',
                'audio' => 'mp3',
                'video' => 'mp4',
                'documento' => 'pdf',
                default => 'bin',
            };

        // Pega o nome base sem extensão
        $nomeBase = $nomeOriginal
            ? pathinfo($nomeOriginal, PATHINFO_FILENAME)
            : $tipo;

        // Sanitiza nome para evitar problemas (sem espaços, barras etc)
        $nomeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nomeBase);

        // Garante nome único com timestamp e uniqid
        $nomeArquivo = $nomeBase . '_' . time() . '_' . uniqid() . '.' . $extensao;

        $pastaTipo = match ($tipo) {
            'imagem' => 'imagens',
            'audio' => 'audios',
            'video' => 'videos',
            'documento' => 'documentos',
            default => 'outros',
        };

        $caminhoCompleto = "uploads/$numeroCliente/$pastaTipo";

        Storage::disk('public')->put("$caminhoCompleto/$nomeArquivo", base64_decode($base64));

        return "$caminhoCompleto/$nomeArquivo";
    }
}
