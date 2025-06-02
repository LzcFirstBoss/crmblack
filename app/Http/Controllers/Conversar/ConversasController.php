<?php

namespace App\Http\Controllers\Conversar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\Http;

class ConversasController extends Controller
{
    public function index($numero = null)
    {
        $sub = Mensagem::selectRaw('MAX(id) as id')
            ->groupBy('numero_cliente');
    
        $contatos = Mensagem::whereIn('id', $sub->pluck('id'))
            ->with('usuario')
            ->orderBy('data_e_hora_envio', 'desc')
            ->get();
    
        $cliente = null;
        $mensagens = collect();
    
        if ($numero) {
            $numeroFormatado = $numero . '@s.whatsapp.net';
            $cliente = Cliente::where('telefoneWhatsapp', $numeroFormatado)->first();
            $mensagens = Mensagem::where('numero', $numero)
            ->with(['mensagem_respondida', 'usuario']) // <- carrega a resposta e o usuário de uma vez
            ->orderBy('data_e_hora_envio')
            ->get();
        }
    
        return view('conversas.index', compact('contatos', 'mensagens', 'cliente', 'numero'));
    }
    

    public function parcial()
    {
        $sub = Mensagem::selectRaw('MAX(id) as id')
            ->groupBy('numero_cliente');

        $contatos = Mensagem::whereIn('id', $sub->pluck('id'))
            ->with('usuario') // ← aqui também é obrigatório para trazer o nome
            ->orderBy('data_e_hora_envio', 'desc')
            ->get();

        foreach ($contatos as $contato) {
            $numeroFormatado = $contato->numero_cliente . '@s.whatsapp.net';
            $cliente = \App\Models\Cliente\Cliente::where('telefoneWhatsapp', $numeroFormatado)->first();

            $contato->qtd_mensagens_novas = $cliente->qtd_mensagens_novas ?? 0;
            $contato->nome_cliente = $cliente->nome ?? null;
        }

        return view('conversas._parcial', compact('contatos'));
    }
     
    public function parcialItem($numero)
    {
        $numero = preg_replace('/[^0-9]/', '', $numero); // limpeza de segurança
        $numeroComDominio = $numero . '@s.whatsapp.net';

        $mensagem = Mensagem::where('numero_cliente', $numero)
            ->orderByDesc('data_e_hora_envio')
            ->with('usuario')
            ->first();

        if (!$mensagem) return '';

        $cliente = Cliente::where('telefoneWhatsapp', $numeroComDominio)->first();

        $mensagem->qtd_mensagens_novas = $cliente->qtd_mensagens_novas ?? 0;
        $mensagem->nome_cliente = $cliente->nome ?? null;

        return view('conversas._parcial_item', ['contato' => $mensagem]);
    }

    public function historico($numero)
    {
        $mensagens = Mensagem::where('numero_cliente', $numero)
            ->with(['usuario', 'mensagem_respondida'])
            ->orderBy('data_e_hora_envio', 'asc')
            ->get();

        return view('conversas._historico', compact('mensagens'));
    }

    public function zerarMensagensNovas($numero)
    {
        $numeroFormatado = $numero . '@s.whatsapp.net';

        $cliente = \App\Models\Cliente\Cliente::where('telefoneWhatsapp', $numeroFormatado)->first();

        if ($cliente) {
            $cliente->qtd_mensagens_novas = 0;
            $cliente->save();

            // Notifica o WebSocket
            Http::post('http://localhost:3001/enviar', [
                'evento' => 'kanban:zerarNotificacao',
                'dados' => [
                    'numero' => $numero  // número sem @s.whatsapp.net
                ]
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
