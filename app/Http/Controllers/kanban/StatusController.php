<?php

namespace App\Http\Controllers\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kanban\Status;

class StatusController extends Controller
{
    public function store(Request $request)
    {
        Status::create(['nome' => $request->nome]);
        return response()->json(['status' => 'criado']);
    }

    public function destroy($id)
    {
        Status::where('id', $id)->delete();
        return response()->json(['status' => 'removido']);
    }
}

