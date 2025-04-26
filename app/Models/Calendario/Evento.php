<?php

namespace App\Models\Calendario;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'descricao',
        'inicio',
        'fim',
        'numerocliente',
    ];

    public $timestamps = true;
}
