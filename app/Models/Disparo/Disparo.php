<?php

namespace App\Models\Disparo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disparo extends Model
{
    protected $fillable = [
        'modelo_mensagem',
        'user_id',
        'numeros',
        'status',
        'titulo',
        'uid_disparo',
    ];

    protected $casts = [
        'numeros' => 'array',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}