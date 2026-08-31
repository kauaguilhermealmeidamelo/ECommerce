<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail simples (texto puro, sem view Blade) com o código de 6 dígitos
 * pra login administrativo com 2FA ativado. Ver AuthController::login().
 */
class CodigoDoisFatoresMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $codigo)
    {
    }

    public function build(): self
    {
        return $this->subject('Seu código de acesso ao painel')
            ->text('emails.codigo-dois-fatores-texto')
            ->with(['codigo' => $this->codigo]);
    }
}
