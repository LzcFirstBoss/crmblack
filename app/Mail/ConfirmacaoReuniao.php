<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoReuniao extends Mailable
{
    use Queueable, SerializesModels;

    public $titulo;
    public $inicio;
    public $fim;

    public function __construct($titulo, $inicio, $fim)
    {
        $this->titulo = $titulo;
        $this->inicio = $inicio;
        $this->fim = $fim;
    }

    public function build()
    {
        return $this->subject('Confirmação da sua reunião - Zabulon')
        ->view('emails.confirmacao-reuniao');
    }
}

