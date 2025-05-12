<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kanban\Status;
use App\Models\Cliente\Cliente;
use Illuminate\Support\Facades\Validator;


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

        return response()->json(['status' => 'removido com sucesso']);
    }
}

