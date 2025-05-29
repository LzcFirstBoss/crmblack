<?php

namespace App\Http\Controllers\Calendario;

use App\Http\Controllers\Controller;
use App\Models\Calendario\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmacaoReuniao;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\Notificacao\Notificacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    private function validarChave(Request $request)
    {
        $apiKey = $request->header('apikey');

        if (!$apiKey || $apiKey !== config('app.APIKEY_SECRET')) {
            throw new \Exception('Não autorizado', 401);
        }
    }

    public function horariosDisponiveis(Request $request)
    {
        $this->validarChave($request);

        $inicio = $request->input('inicio');
        $fim = $request->input('fim');

        if (!$inicio || !$fim) {
            return response()->json(['erro' => 'Parâmetros "inicio" e "fim" são obrigatórios.'], 400);
        }

        $inicio = Carbon::parse($inicio);
        $fim = Carbon::parse($fim);

        $conflito = Evento::where(function ($query) use ($inicio, $fim) {
            $query->whereBetween('inicio', [$inicio, $fim])
                ->orWhereBetween('fim', [$inicio, $fim])
                ->orWhere(function ($query) use ($inicio, $fim) {
                    $query->where('inicio', '<', $inicio)
                            ->where('fim', '>', $fim);
                });
        })->exists();

        return response()->json([
            'disponivel' => !$conflito,
            'verificado' => [
                'inicio' => $inicio->format('Y-m-d H:i'),
                'fim' => $fim->format('Y-m-d H:i'),
            ]
        ]);
    }

        public function agendarReuniao(Request $request)
        {
            try {
                $this->validarChave($request);

                $validator = Validator::make($request->all(), [
                    'titulo' => 'required|string|max:255',
                    'descricao' => 'nullable|string',
                    'inicio' => 'required|date',
                    'fim' => 'required|date|after:inicio',
                    'telefoneWhatsapp' => 'required|string',
                    'email' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'erro' => 'Validação falhou',
                        'mensagens' => $validator->errors()
                    ], 422);
                }

                // Verifica conflito
                $conflito = Evento::where(function ($q) use ($request) {
                    $q->whereBetween('inicio', [$request->inicio, $request->fim])
                    ->orWhereBetween('fim', [$request->inicio, $request->fim]);
                })->exists();

                if ($conflito) {
                    return response()->json([
                        'erro' => 'Horário já ocupado.'
                    ], 409);
                }

                // Gera o número de atendimento
                $n_atendimento = 'AT-' . strtoupper(Str::random(4));

                // Cria o evento
                $evento = Evento::create([
                    'titulo' => $request->titulo,
                    'descricao' => $request->descricao,
                    'inicio' => $request->inicio,
                    'fim' => $request->fim,
                    'numerocliente' => $request->telefoneWhatsapp,
                    'n_atendimento' => $n_atendimento,
                ]);

                // Atualiza cliente
                $cliente = Cliente::where('telefoneWhatsapp', $request->telefoneWhatsapp)->first();
                if ($cliente) {
                    $cliente->email = $request->email;
                    $cliente->save();
                } else {
                    return response()->json([
                        'erro' => 'Cliente não encontrado com esse telefoneWhatsapp.'
                    ], 404);
                }

                // Envia e-mail se existir
                if ($request->email) {
                    Mail::to($request->email)->send(new ConfirmacaoReuniao(
                        $request->titulo,
                        $request->inicio,
                        $request->fim
                    ));
                }

                $numero = $request->telefoneWhatsapp;

                $usuarios = User::all();
                $linkNumero = preg_replace('/@s\.whatsapp\.net$/', '', $evento->numerocliente);

                foreach ($usuarios as $usuario) {
                    Notificacao::create([
                        'user_id' => $usuario->id,
                        'titulo' => 'Consulta agendada',
                        'mensagem' => 'Uma nova Consulta foi agendada para ' . \Carbon\Carbon::parse($request->inicio)->format('d/m/Y H:i'),
                        'tipo' => 'reuniao_agendada',
                        'link' => $linkNumero,
                        'dados' => json_encode(['n_atendimento' => $evento->n_atendimento])
                    ]);
                }

               // Envia para WebSocket
                Http::post('http://localhost:3001/enviar', [
                    'evento' => 'novaNotificacao',
                    'dados' => [
                        'titulo' => 'Consulta Agendada',
                        'mensagem' => 'A Consulta com o número ' . $numero . ' foi agendada.',
                        'tipo' => 'reuniao_agendada',
                        'created_at' => now()->toISOString()
                    ]
                ]);

                return response()->json([
                    'mensagem' => 'Consulta agendada com sucesso!',
                    'n_atendimento' => $n_atendimento,
                    'evento' => $evento
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'erro' => 'Erro interno no servidor',
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile()
                ], 500);
            }
        }
    
        public function reagendarReuniao(Request $request)
        {
            try {
                $this->validarChave($request);

                $validator = Validator::make($request->all(), [
                    'n_atendimento' => 'required|string|exists:eventos,n_atendimento',
                    'inicio' => 'required|date',
                    'fim' => 'required|date|after:inicio',
                ]);

                if ($validator->fails()) {
                    $mensagens = $validator->errors();

                    if ($mensagens->has('n_atendimento')) {
                        return response()->json([
                            'erro' => 'Código de atendimento inválido ou não encontrado.'
                        ], 404);
                    }

                    return response()->json([
                        'erro' => 'Validação falhou',
                        'mensagens' => $mensagens
                    ], 422);
                }
                
                $evento = Evento::where('n_atendimento', $request->n_atendimento)->first();

                // Verifica conflito com outros eventos
                $conflito = Evento::where('id', '!=', $evento->id)
                    ->where(function ($q) use ($request) {
                        $q->whereBetween('inicio', [$request->inicio, $request->fim])
                            ->orWhereBetween('fim', [$request->inicio, $request->fim])
                            ->orWhere(function ($q) use ($request) {
                                $q->where('inicio', '<', $request->inicio)
                                    ->where('fim', '>', $request->fim);
                            });
                    })->exists();

                if ($conflito) {
                    return response()->json([
                        'erro' => 'Horário já ocupado.'
                    ], 409);
                }

                // Atualiza o evento
                $evento->inicio = $request->inicio;
                $evento->fim = $request->fim;
                $evento->save();

                $usuarios = User::all();

                $linkNumero = preg_replace('/@s\.whatsapp\.net$/', '', $evento->numerocliente);

                foreach ($usuarios as $usuario) {
                    Notificacao::create([
                        'user_id' => $usuario->id,
                        'titulo' => 'Consulta remarcada',
                        'mensagem' => 'Uma Consulta foi remarcada para ' . \Carbon\Carbon::parse($request->inicio)->format('d/m/Y H:i'),
                        'tipo' => 'reuniao_remarcada',
                        'link' =>  $linkNumero,
                        'dados' => json_encode(['n_atendimento' => $evento->n_atendimento])
                    ]);
                }

                Http::post('http://localhost:3001/enviar', [
                    'evento' => 'novaNotificacao',
                    'dados' => [
                        'id' => rand(10000, 99999),
                        'titulo' => 'Consulta remarcada',
                        'mensagem' => 'Uma Consulta foi remarcada para ' . \Carbon\Carbon::parse($request->inicio)->format('d/m/Y H:i'),
                        'tipo' => 'reuniao_remarcada',
                        'created_at' => now()->toISOString()
                    ]
                ]); 

                return response()->json([
                    'mensagem' => 'Consulta remarcada com sucesso.',
                    'n_atendimento' => $evento->n_atendimento,
                    'novo_inicio' => $evento->inicio,
                    'novo_fim' => $evento->fim
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'erro' => 'Erro ao remarcar Consulta',
                    'detalhes' => $e->getMessage()
                ], 500);
            }
        }

        public function cancelarReuniao(Request $request)
        {
            try {
                $this->validarChave($request);

                $validator = Validator::make($request->all(), [
                    'n_atendimento' => 'required|string|exists:eventos,n_atendimento',
                ]);

                if ($validator->fails()) {
                    $mensagens = $validator->errors();

                    if ($mensagens->has('n_atendimento')) {
                        return response()->json([
                            'erro' => 'Código de atendimento inválido ou não encontrado.'
                        ], 404);
                    }

                    return response()->json([
                        'erro' => 'Validação falhou',
                        'mensagens' => $mensagens
                    ], 422);
                }   

                $evento = Evento::where('n_atendimento', $request->n_atendimento)->first();

                if (!$evento) {
                    return response()->json(['erro' => 'Evento não encontrado.'], 404);
                }

                $numero = preg_replace('/@s\.whatsapp\.net$/', '', $evento->numerocliente);

                // Remove o evento do calendário
                $evento->delete();

                // Notifica todos os usuários
                $usuarios = User::all();
                foreach ($usuarios as $usuario) {
                    Notificacao::create([
                        'user_id' => $usuario->id,
                        'titulo' => 'Consulta cancelada',
                        'mensagem' => 'A Consulta com o número ' . $numero . ' foi cancelada.',
                        'tipo' => 'reuniao_cancelada',
                        'dados' => json_encode(['n_atendimento' => $request->n_atendimento, 'numero' => $numero])
                    ]);
                }

                // Envia para WebSocket
                Http::post('http://localhost:3001/enviar', [
                    'evento' => 'novaNotificacao',
                    'dados' => [
                        'titulo' => 'Consulta cancelada',
                        'mensagem' => 'A Consulta com o número ' . $numero . ' foi cancelada.',
                        'tipo' => 'reuniao_cancelada',
                        'created_at' => now()->toISOString()
                    ]
                ]);

                return response()->json([
                    'mensagem' => 'Consulta cancelada com sucesso.',
                    'n_atendimento' => $request->n_atendimento
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'erro' => 'Erro ao cancelar Consulta',
                    'detalhes' => $e->getMessage()
                ], 500);
            }
        }

        public function listarFunis(Request $request)
        {
            try {
                $this->validarChave($request);

                $funis = DB::table('status')->select('id', 'nome', 'descricao')->get();

                return response()->json($funis);

            } catch (\Exception $e) {
                return response()->json([
                    'erro' => 'Erro ao listar funis',
                    'mensagem' => $e->getMessage(),
                    'linha' => $e->getLine(),
                    'arquivo' => $e->getFile()
                ], $e->getCode() ?: 500);
            }
        }

        public function mudarFunilCliente(Request $request)
        {
            try {
                $this->validarChave($request);

                $validator = Validator::make($request->all(), [
                    'telefoneWhatsapp' => 'required|string',
                    'status_id' => 'required|integer|exists:status,id',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'erro' => 'Validação falhou',
                        'mensagens' => $validator->errors()
                    ], 422);
                }

                $cliente = Cliente::where('telefoneWhatsapp', $request->telefoneWhatsapp)->first();

                if (!$cliente) {
                    return response()->json(['erro' => 'Cliente não encontrado.'], 404);
                }

                $cliente->status_id = $request->status_id;
                $cliente->save();

                Http::post('http://localhost:3001/enviar', [
                    'evento' => 'kanban:moverPorNumero',
                    'dados' => [
                        'numero' => preg_replace('/@s\.whatsapp\.net$/', '', $cliente->telefoneWhatsapp),
                        'status' => $cliente->status_id
                    ]
                ]);

                return response()->json([
                    'mensagem' => 'Funil do cliente atualizado com sucesso.',
                    'cliente' => [
                        'nome' => $cliente->nome ?? null,
                        'telefoneWhatsapp' => $cliente->telefoneWhatsapp,
                        'status_id' => $cliente->status_id
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'erro' => 'Erro interno',
                    'mensagem' => $e->getMessage()
                ], 500);
            }
        }
}
