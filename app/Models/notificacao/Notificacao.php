<?php

namespace App\Models\notificacao;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class notificacao extends Model
{
    use HasFactory;

     protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensagem',
        'lida',
        'tipo',
        'dados',
        'link',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'dados' => 'array',
    ];

    // Relacionamento com User (opcional, mas útil)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
