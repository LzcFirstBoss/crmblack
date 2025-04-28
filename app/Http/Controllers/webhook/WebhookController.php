<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Webhook\Mensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            $tipoOriginal = $mensagem['tipo_de_mensagem'] ?? '';

            if (!$this->tipoEhValido($tipoOriginal)) {
                continue;
            }

            $tipo = $this->tipoNormalizado($tipoOriginal);
            $caminhoArquivo = null;

            if (in_array($tipo, ['imagem', 'audio', 'video']) && !empty($mensagem['base64'])) {
                $caminhoArquivo = $this->salvarArquivoBase64(
                    $mensagem['base64'],
                    $tipo,
                    $mensagem['numero_cliente']
                );
            }

            // Incrementar qtd_mensagens_novas
            if (
                isset($mensagem['numero_cliente']) && 
                !$this->foiEnviadoPorMimOuBot($mensagem)
            ) {
                $numeroCliente = $mensagem['numero_cliente'] . '@s.whatsapp.net'; // 👈 Adiciona o @

                $cliente = Cliente::where('telefoneWhatsapp', $numeroCliente)->first();

                if ($cliente) {
                    $cliente->increment('qtd_mensagens_novas');
                } else {
                    Cliente::create([
                        'telefoneWhatsapp' => $numeroCliente,
                        'qtd_mensagens_novas' => 1,
                    ]);
                }
            }

            // SALVAR a mensagem normalmente
            Mensagem::create([
                'numero_cliente'     => $mensagem['numero_cliente'] ?? '',
                'tipo_de_mensagem'   => $tipoOriginal,
                'mensagem_enviada'   => $caminhoArquivo ?? ($mensagem['mensagem_enviada'] ?? null),
                'data_e_hora_envio'  => $mensagem['data_e_hora_envio'] ?? now(),
                'enviado_por_mim'    => filter_var($mensagem['enviado_por_mim'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'usuario_id'         => $mensagem['usuario_id'] ?? null,
                'bot'                => filter_var($mensagem['bot'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        return response()->json(['status' => 'mensagem(ns) salva(s) com sucesso']);
    }

    private function tipoEhValido(string $tipoOriginal): bool
    {
        return in_array($tipoOriginal, [
            'conversation',
            'extendedTextMessage',
            'imageMessage',
            'audioMessage',
            'videoMessage',
        ]);
    }

    private function tipoNormalizado(string $tipoOriginal): string
    {
        return match ($tipoOriginal) {
            'conversation', 'extendedTextMessage' => 'texto',
            'imageMessage'  => 'imagem',
            'audioMessage'  => 'audio',
            'videoMessage'  => 'video',
            default         => 'desconhecido',
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
            'audio'  => 'mp3',
            'video'  => 'mp4',
            default  => 'bin',
        };

        $pastaTipo = match ($tipo) {
            'imagem' => 'imagens',
            'audio'  => 'audios',
            'video'  => 'videos',
            default => 'outros',
        };

        $caminhoCompleto = "uploads/$numeroCliente/$pastaTipo";
        $nomeArquivo = uniqid() . '.' . $extensao;

        Storage::disk('public')->put("$caminhoCompleto/$nomeArquivo", base64_decode($base64));

        return "$caminhoCompleto/$nomeArquivo";
    }
}
