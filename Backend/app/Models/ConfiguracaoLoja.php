<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoLoja extends Model
{
    protected $table = 'configuracoes_loja';

    protected $fillable = ['produto_expira_apos_venda'];

    protected $casts = ['produto_expira_apos_venda' => 'boolean'];
}
