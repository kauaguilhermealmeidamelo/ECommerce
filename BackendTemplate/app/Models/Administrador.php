<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthenticatableModel;
use Laravel\Sanctum\HasApiTokens;

class Administrador extends AuthenticatableModel
{
    protected $table = 'administradores';
    
    use HasApiTokens;
    protected $fillable = ['nome', 'email', 'senha_hash', 'papel'];

    protected $hidden = ['senha_hash', 'remember_token'];

    public function getAuthPassword(): ?string
    {
        return $this->senha_hash;
    }
}
