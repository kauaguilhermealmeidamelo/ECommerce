<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEntrega extends Model
{
    protected $table = 'configuracoes_entrega';

    protected $fillable = ['retirada_ativa', 'entrega_local_ativa', 'transportadora_ativa', 'token_melhor_envio'];

    protected $casts = [
        'retirada_ativa' => 'boolean',
        'entrega_local_ativa' => 'boolean',
        'transportadora_ativa' => 'boolean',
    ];
}
