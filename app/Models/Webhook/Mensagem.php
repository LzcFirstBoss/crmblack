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
        'id_mensagem',
        'tipo_de_mensagem',
        'mensagem_enviada',
        'base64',
        'data_e_hora_envio',
        'enviado_por_mim',
        'usuario_id',
        'bot',
        'status',
        'mensagem_respondida_id',
    ];

    protected $casts = [
        'data_e_hora_envio' => 'datetime',
        'enviado_por_mim' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function mensagem_respondida()
    {
        return $this->belongsTo(Mensagem::class, 'mensagem_respondida_id');
    }

        public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente\Cliente::class, 'numero_cliente', 'telefoneWhatsapp');
    }

        public function getUltimaMensagemClienteAttribute()
    {
        $numeroLimpo = preg_replace('/@.*/', '', $this->telefoneWhatsapp);

        $mensagem = Mensagem::where('numero_cliente', $numeroLimpo)
            ->where('enviado_por_mim', false)
            ->orderByDesc('data_e_hora_envio')
            ->first();

        return $mensagem?->data_e_hora_envio;
    }
}
