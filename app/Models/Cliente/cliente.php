<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'Clientes'; // nome da tabela

    protected $fillable = [
        'telefoneWhatsapp',
        'botativo',
        'email',
        'qtd_mensagens_novas',
        'nome',
        'status_id',
    ];

    protected $casts = [
        'botativo' => 'boolean', // garante true/false correto
    ];

    public $timestamps = false; // se sua tabela não usa created_at/updated_at
}
