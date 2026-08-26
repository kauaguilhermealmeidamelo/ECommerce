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

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
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
