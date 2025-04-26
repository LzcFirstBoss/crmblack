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
    ];

    protected $casts = [
        'botativo' => 'boolean', // garante true/false correto
    ];

    public $timestamps = false; // se sua tabela não usa created_at/updated_at
}
