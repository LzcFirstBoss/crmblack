<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'bots';

    protected $fillable = [
        'id_user',
        'nome',
        'descricao',
        'prompt',
        'funcoes',
        'ativo',
        'lixeira',
    ];

    protected $casts = [
        'funcoes' => 'array',
        'ativo' => 'boolean',
        'lixeira' => 'boolean',
    ];
}
