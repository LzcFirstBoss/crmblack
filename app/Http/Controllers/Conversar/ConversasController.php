<?php

namespace App\Http\Controllers\Conversar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\DB;

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
            $mensagens = Mensagem::where('numero', $numero)->orderBy('data_e_hora_envio')->get();
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
            $contato->qtd_mensagens_novas = optional(
                \App\Models\Cliente\Cliente::where('telefoneWhatsapp', $numeroFormatado)->first()
            )->qtd_mensagens_novas ?? 0;
        }

        return view('conversas._parcial', compact('contatos'));
    }
      

    public function historico($numero)
    {
        // Carrega o histórico de um número específico
        $mensagens = Mensagem::where('numero_cliente', $numero)
            ->with('usuario')
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
        }

        return response()->json(['status' => 'ok']);
    }
}
