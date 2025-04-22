<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Model;

class FuncoesBot extends Model
{
    protected $table = 'funcoes_bot';

    protected $fillable = [
        'nome',
        'descricao',
        'prompt',
    ];
}
