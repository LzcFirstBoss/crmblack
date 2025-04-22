<?php

namespace App\Http\Controllers\Bots;

use App\Http\Controllers\Controller;
use App\Models\Bot\Bot;
use App\Models\Bot\FuncoesBot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'required|string|max:300',
            'prompt' => 'required|string',
            'funcoes' => 'array',
            'funcoes.*' => 'integer|exists:funcoes_bot,id',
        ]);
    
        // Recupera prompts das funções selecionadas
        $funcoesSelecionadas = $request->input('funcoes', []);
        $promptsDasFuncoes = FuncoesBot::whereIn('id', $funcoesSelecionadas)
            ->pluck('prompt')
            ->toArray();
    
        // Junta o prompt base com as funções
        $promptFinal = $request->prompt . "\n\n" . implode("\n\n", $promptsDasFuncoes);
    
        if ($request->has('ativo')) {
            Bot::where('id_user', auth()->id())->update(['ativo' => false]);
        }

        // Cria o bot
        Bot::create([
            'id_user' => auth()->id(),
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'prompt' => $promptFinal,
            'funcoes' => $funcoesSelecionadas, // campo JSON na tabela
            'ativo' => $request->has('ativo'),
        ]);
    
        return redirect()->back()->with('success', 'Bot criado com sucesso!');
    }

    public function destroy($id)
    {
        $bot = Bot::findOrFail($id);
    
        if ($bot->ativo) {
            return redirect()->back()->with('error', 'Você não pode deletar um bot que está ativo.');
        }
    
        $bot->update(['lixeira' => true]); // <- esse comando deve funcionar
    
        return redirect()->back()->with('success', 'Bot movido para a lixeira com sucesso!');
    }

    public function update(Request $request, $id)
{
    $bot = Bot::findOrFail($id);

    $request->validate([
        'nome' => 'required|string|max:255',
        'descricao' => 'required|string|max:300',
        'prompt' => 'required|string',
        'funcoes' => 'array',
    ]);

    $funcoesAtivas = $request->input('funcoes', []);
    $promptBase = $request->input('prompt');

    // Junta todos os prompts das funções ativas
    $promptFuncoes = FuncoesBot::whereIn('id', $funcoesAtivas)->pluck('prompt')->implode("\n\n");

    // Atualiza todos os bots para inativo se esse for ativo
    if ($request->has('ativo')) {
        Bot::where('id', '!=', $bot->id)->update(['ativo' => false]);
        $bot->ativo = true;
    } else {
        $bot->ativo = false;
    }

    // Atualiza os campos
    $bot->nome = $request->nome;
    $bot->descricao = $request->descricao;
    $bot->prompt = $promptBase . "\n\n" . $promptFuncoes;
    $bot->funcoes = $funcoesAtivas;

    $bot->save();

    return redirect()->back()->with('success', 'Bot atualizado com sucesso!');
}

}
