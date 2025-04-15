<?php

namespace App\Http\Controllers\historico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\http;


class HistoricoConversaController extends Controller
{
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

        $numeroFormatado = $numero . '@s.whatsapp.net';

        $cliente = Cliente::where('telefoneWhatsapp', $numeroFormatado)->first();

        return view('kanban.historico', compact('numero', 'fotoPerfil', 'cliente'));
    }
    
    public function atualizarHistorico($numero)
    {
        $mensagens = Mensagem::where('numero_cliente', $numero)
        ->with('usuario') // ← carrega o usuário vinculado à mensagem
        ->orderBy('data_e_hora_envio', 'asc')
        ->get();
    
        return view('kanban._mensagens', compact('mensagens'));
    }

    public function alternar(Request $request)
    {
        $numero = $request->query('numero');

        if (!$numero) {
            return redirect()->back()->with('erro', 'Número não informado');
        }

        $numeroFormatado = $numero . '@s.whatsapp.net';
        $cliente = Cliente::where('telefoneWhatsapp', $numeroFormatado)->first();

        if (!$cliente) {
            return redirect()->back()->with('erro', 'Cliente não encontrado');
        }

        $cliente->botativo = !$cliente->botativo;
        $cliente->save();

        return redirect()->back()->with('sucesso', 'Bot ' . ($cliente->botativo ? 'ativado' : 'desativado') . ' com sucesso!');
    }
}
