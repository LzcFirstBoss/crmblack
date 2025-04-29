<?php

namespace App\Http\Controllers\Conversar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Facades\DB;

class ConversasController extends Controller
{
    public function index()
    {
        $sub = Mensagem::selectRaw('MAX(id) as id')
            ->where('enviado_por_mim', false)
            ->groupBy('numero_cliente');
    
        $contatos = Mensagem::whereIn('id', $sub->pluck('id'))
            ->orderBy('data_e_hora_envio', 'desc')
            ->get();
    
        // Novo trecho: carregar manualmente os clientes
        foreach ($contatos as $contato) {
            $numeroFormatado = $contato->numero_cliente . '@s.whatsapp.net';
            $contato->qtd_mensagens_novas = optional(
                \App\Models\Cliente\Cliente::where('telefoneWhatsapp', $numeroFormatado)->first()
            )->qtd_mensagens_novas ?? 0;
        }
    
        return view('conversas.index', compact('contatos'));
    }
    
    public function parcial()
    {
        $sub = Mensagem::selectRaw('MAX(id) as id')
            ->where('enviado_por_mim', false)
            ->groupBy('numero_cliente');
    
        $contatos = Mensagem::whereIn('id', $sub->pluck('id'))
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
