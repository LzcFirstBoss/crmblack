<?php

namespace App\Models\Webhook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    use HasFactory;

    protected $table = 'mensagens';

    protected $fillable = [
        'numero_cliente',
        'tipo_de_mensagem',
        'mensagem_enviada',
        'base64',
        'data_e_hora_envio',
        'enviado_por_mim',
        'usuario_id',
        'bot',
    ];

    protected $casts = [
        'data_e_hora_envio' => 'datetime',
        'enviado_por_mim' => 'boolean',
    ];
}
