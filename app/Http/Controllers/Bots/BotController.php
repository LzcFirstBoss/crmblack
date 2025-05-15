<?php

namespace App\Http\Controllers\Bots;

use App\Http\Controllers\Controller;
use App\Models\Bot\Bot;
use App\Models\Bot\FuncoesBot;
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

        $funcoesSelecionadas = $request->input('funcoes', []);
        $promptFinal = $request->prompt;

        if ($request->has('ativo')) {
            // Desativa todos os bots do sistema
            Bot::query()->update(['ativo' => false]);
        }

        Bot::create([
            'id_user' => auth()->id(),
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'prompt' => $promptFinal,
            'funcoes' => $funcoesSelecionadas,
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

        $bot->update(['lixeira' => true]);

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

        if ($request->has('ativo')) {
            // Desativa todos os bots do sistema, menos o atual
            Bot::where('id', '!=', $bot->id)->update(['ativo' => false]);
            $bot->ativo = true;
        } else {
            $bot->ativo = false;
        }

        $bot->nome = $request->nome;
        $bot->descricao = $request->descricao;
        $bot->prompt = $promptBase;
        $bot->funcoes = $funcoesAtivas;

        $bot->save();

        return redirect()->back()->with('success', 'Bot atualizado com sucesso!');
    }
}
