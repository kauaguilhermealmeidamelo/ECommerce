<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as AuthenticatableModel;
use Laravel\Sanctum\HasApiTokens;

// Estende o model de autenticação do Laravel para poder usar Sanctum e login.
class Cliente extends AuthenticatableModel
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['nome', 'email', 'telefone', 'senha_hash', 'email_verificado_em'];

    protected $hidden = ['senha_hash', 'remember_token'];

    // Sanctum/Auth esperam o campo "password" — mapeamos para senha_hash.
    public function getAuthPassword(): ?string
    {
        return $this->senha_hash;
    }

    public function provedores(): HasMany
    {
        return $this->hasMany(ClienteProvedor::class);
    }

    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }

    public function cartoesSalvos(): HasMany
    {
        return $this->hasMany(CartaoSalvo::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function carrinho()
    {
        return $this->hasOne(Carrinho::class);
    }
}
