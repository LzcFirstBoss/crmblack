<?php

namespace App\Models\Kanban;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    // Nome da tabela (se não seguir o padrão pluralizado)
    protected $table = 'status';

    // Campos que podem ser preenchidos via create()
    protected $fillable = ['nome', 'cor'];

    // Relacionamento com mensagens (opcional, caso use futuramente)
    public function mensagens()
    {
        return $this->hasMany(\App\Models\Webhook\Mensagem::class);
    }
}
