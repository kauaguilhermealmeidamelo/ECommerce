<?php

namespace App\Models;

// ATENÇÃO: STUB. Substitua pelo seu model de usuário real (com Sanctum
// HasApiTokens, relação de endereços, etc.). Mantido mínimo aqui só pra
// esse pacote isolado rodar sozinho.

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin', 'telefone', 'google_id'];

    // Os campos de 2FA nunca são fillable de propósito — só o backend
    // (AuthController, via forceFill) pode escrevê-los. Isso impede que
    // um payload malicioso em qualquer outro endpoint de update de
    // usuário tente "resetar" ou "confirmar" o próprio código de 2FA.
    protected $hidden = [
        'password', 'remember_token',
        'dois_fatores_codigo', 'dois_fatores_expira_em', 'dois_fatores_tentativas',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'dois_fatores_expira_em' => 'datetime',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function carrinho()
    {
        return $this->hasOne(Carrinho::class, 'usuario_id');
    }
}