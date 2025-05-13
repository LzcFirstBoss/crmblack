<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kanban\Status;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class StatusController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255|unique:status,nome',
            'cor' => 'nullable|string|max:7'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        $status = new Status();
        $status->nome = $request->nome;
        $status->cor = $request->cor ?? '#ffffff';
        $status->save();
    
        $this->notificarAtualizacaoKanban();
        return response()->json(['status' => 'criado com sucesso']);
    }
    

    public function destroy($id)
    {
        $status = Status::findOrFail($id);

        if ($status->fixo) {
            return response()->json(['erro' => 'Não é possível excluir este status.'], 403);
        }

        // Encontra o status “Aguardando” (fixo)
        $aguardando = Status::where('fixo', true)->first();

        // Move todas as mensagens para “Aguardando”
        Cliente::where('status_id', $status->id)->update([
            'status_id' => $aguardando->id
        ]);

        $status->delete();
        $this->notificarAtualizacaoKanban();
        return response()->json(['status' => 'removido com sucesso']);
    }

    
    public function atualizarCor(Request $request, $id)
    {
        $status = Status::findOrFail($id);
        $status->cor = $request->cor;
        $status->save();

        $status->cor = $request->cor;
        $status->save();

        $this->notificarAtualizacaoKanban();
        return response()->json(['status' => 'cor atualizada']);
    }

    private function notificarAtualizacaoKanban()
    {
        try {
            Http::post('http://localhost:3001/enviar', [
                'evento' => 'kanban:atualizar',
                'dados' => []
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao notificar WebSocket: ' . $e->getMessage());
        }
    }
}

