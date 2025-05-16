<?php

namespace App\Http\Controllers\Disparo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente\Cliente;
use App\Models\Status\Status;
use App\Models\Disparo\Disparo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DisparoController extends Controller
{
    public function criar()
    {
        $clientes = Cliente::select('nome', 'telefoneWhatsapp')->get();
        $funis = Status::select('id', 'nome', 'cor')->get();

        // contar clientes por funil (status_id)
        $contagemPorFunil = Cliente::selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $disparos = Disparo::latest()->get();

        return view('disparo.criar', compact('clientes', 'funis', 'contagemPorFunil', 'disparos'));
    }

    public function enviar(Request $request)
    {
        $titulo = $request->input('titulo');
        $mensagem = $request->input('mensagem');
        $clientesSelecionados = $request->input('clientes');
        $funilSelecionado = $request->input('funil');

        $numeros = [];

        if (!empty($clientesSelecionados)) {
            $numeros = $clientesSelecionados;
        } elseif (!empty($funilSelecionado)) {
            $numeros = Cliente::where('status_id', $funilSelecionado)
                ->pluck('telefoneWhatsapp')
                ->toArray();
        }

        if (empty($mensagem) || empty($numeros)) {
            return back()->with('erro', 'Preencha a mensagem e selecione os destinatários.');
        }

        // Gera o uid que será usado para rastreamento (não é primary key)
        $uidDisparo = (string) Str::uuid();
        $tokenDisparo = (string) Str::uuid();

        // Montar payload
        $payload = [
            'mensagem' => $mensagem,
            'numeros' => $numeros,
            'apikey' => env('EVOLUTION_API_KEY'),
            'instance' => env('EVOLUTION_INSTANCE_ID'),
            'url_api' => env('EVOLUTION_API_URL'),
            'segredo' => env('APIKEY_SECRET'),
            'token' => $tokenDisparo,
            'titulo' => $titulo,
            'uid_disparo' => $uidDisparo, // << aqui vai o novo ID gerado
        ];

        $webhookUrl = env('URL_WEBHOOK_DISPARO');

        try {
            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                // Se deu tudo certo, agora sim salva o disparo
                Disparo::create([
                    'uid_disparo' => $uidDisparo,
                    'modelo_mensagem' => $mensagem,
                    'user_id' => auth()->id(),
                    'numeros' => $numeros,
                    'status' => 'rodando',
                    'titulo' => $titulo
                ]);

                return back()->with('sucesso', 'Disparo iniciado com sucesso!');
            } else {
                return back()->with('erro', 'Erro na resposta do webhook: ' . $response->body());
            }
        } catch (\Exception $e) {
            return back()->with('erro', 'Erro ao enviar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $disparo = Disparo::findOrFail($id);
        return response()->json($disparo);
    }

    public function cancelar($id)
    {
        $disparo = Disparo::findOrFail($id);

        if ($disparo->status !== 'rodando') {
            return response()->json(['mensagem' => 'Este disparo já foi concluído ou cancelado.']);
        }

        $disparo->update(['status' => 'cancelado']);

        return response()->json(['mensagem' => 'Disparo cancelado com sucesso.']);
    }
}
