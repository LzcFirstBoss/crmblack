<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes'; // ou 'clientes' se esse for o nome correto da tabela

    protected $fillable = [
        'telefoneWhatsapp',
        'botativo',
        // outros campos, se necessário
    ];

    protected $casts = [
        'botativo' => 'boolean',
    ];

    public $timestamps = false; // se sua tabela não tem created_at e updated_at
}