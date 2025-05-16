<?php

namespace App\Models\Status;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status'; // Se o nome da tabela for diferente do padrão (statuses)

    protected $fillable = [
        'nome',
        'cor',
    ];

    public $timestamps = false; // Se a tabela não tem created_at/updated_at
}
