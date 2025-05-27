<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente\Cliente;
use App\Models\Webhook\Mensagem;
use Illuminate\Support\Carbon;

class LeadsController extends Controller
{
    public function index(Request $request)
    {
        $clientes = \App\Models\Cliente\Cliente::query();

        if ($request->filled('busca')) {
            $busca = $request->input('busca');

            $clientes->where(function ($query) use ($busca) {
                $query->where('nome', 'ILIKE', "%{$busca}%")
                    ->orWhere('telefoneWhatsapp', 'ILIKE', "%{$busca}%")
                    ->orWhere('email', 'ILIKE', "%{$busca}%");
            });
        }

        $clientes = $clientes->orderBy('created_at', 'desc')->paginate(10);

        // Última mensagem
        foreach ($clientes as $cliente) {
            $numeroLimpo = preg_replace('/@.*/', '', $cliente->telefoneWhatsapp);
            $ultimaMensagem = Mensagem::where('numero_cliente', $numeroLimpo)
                ->where('enviado_por_mim', false)
                ->orderByDesc('data_e_hora_envio')
                ->first();
            $cliente->ultima_mensagem_cliente = $ultimaMensagem?->data_e_hora_envio;
        }

        return view('leads.index', compact('clientes'));
    }


    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);

        $numeroLimpo = preg_replace('/@.*/', '', $cliente->telefoneWhatsapp);

        $ultimaMensagemCliente = Mensagem::where('numero_cliente', $numeroLimpo)
            ->where('enviado_por_mim', false)
            ->orderByDesc('data_e_hora_envio')
            ->first();

        return view('leads.show', compact('cliente', 'ultimaMensagemCliente'));
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('leads.edit', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'telefoneWhatsapp' => 'required|string|max:30',
            'email' => 'nullable|email',
        ]);

        $cliente->update($request->all());

        return redirect()->route('leads.show', $cliente->id)->with('success', 'Lead atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        // Remove "@s.whatsapp.net" do número para buscar as mensagens relacionadas
        $numeroLimpo = preg_replace('/@.*/', '', $cliente->telefoneWhatsapp);

        // Deleta todas as mensagens com esse número
        Mensagem::where('numero_cliente', $numeroLimpo)->delete();

        // Deleta o cliente
        $cliente->delete();

        return redirect()->route('leads.index')->with('success', 'Lead e mensagens excluídos com sucesso!');
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefoneWhatsapp' => 'required|string|max:30',
            'email' => 'nullable|email',
            'empresa' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'origem' => 'nullable|string',
            'valor_estimado' => 'nullable|numeric',
            'tags' => 'nullable|string',
            'notas' => 'nullable|string',
        ], [
            'telefoneWhatsapp.required' => 'O telefone é obrigatório.',
            'telefoneWhatsapp.unique' => 'Este número já está cadastrado.',
        ]);

        // Normaliza telefone
        $numero = preg_replace('/\D/', '', $request->telefoneWhatsapp); // remove não dígitos

        if (!str_starts_with($numero, '55')) {
            $numero = '55' . $numero;
        }

        $numeroCompleto = $numero . '@s.whatsapp.net';

        // Verifica duplicidade manual, já que vamos inserir customizado
        if (Cliente::where('telefoneWhatsapp', $numeroCompleto)->exists()) {
            return back()
                ->withErrors(['telefoneWhatsapp' => 'Este número já está cadastrado.'])
                ->withInput();
        }

        // Cria o cliente
        Cliente::create([
            'nome' => $request->nome,
            'telefoneWhatsapp' => $numeroCompleto,
            'email' => $request->email,
            'empresa' => $request->empresa,
            'status' => $request->status,
            'origem' => $request->origem,
            'valor_estimado' => $request->valor_estimado,
            'tags' => $request->tags,
            'notas' => $request->notas,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead criado com sucesso!');
    }
}
