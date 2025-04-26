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
    
        $diaBase = $request->input('dia');
    
        if (!$diaBase) {
            return response()->json(['erro' => 'Parâmetro "dia" é obrigatório.'], 400);
        }
    
        $inicio = Carbon::parse($diaBase)->startOfDay();
        $fim = $inicio->copy()->addDays(6)->endOfDay();
    
        $eventos = Evento::whereBetween('inicio', [$inicio, $fim])->get();
    
        $ocupados = [];
    
        foreach ($eventos as $evento) {
            $ocupados[] = [
                'data' => Carbon::parse($evento->inicio)->format('Y-m-d'),
                'inicio' => Carbon::parse($evento->inicio)->format('H:i'),
                'fim' => Carbon::parse($evento->fim)->format('H:i'),
            ];
        }
    
        return response()->json($ocupados);
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
                'email' => 'required|email',
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
    
            // Cria o evento
            $evento = Evento::create([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'inicio' => $request->inicio,
                'fim' => $request->fim,
                'numerocliente' => $request->telefoneWhatsapp,
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
    
            // Envia e-mail
            Mail::to($request->email)->send(new ConfirmacaoReuniao(
                $request->titulo,
                $request->inicio,
                $request->fim
            ));
    
            return response()->json([
                'mensagem' => 'Reunião agendada com sucesso!',
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
    
}
