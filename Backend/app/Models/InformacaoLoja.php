<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformacaoLoja extends Model
{
    protected $table = 'informacoes_loja';

    protected $fillable = [
        'nome', 'telefone', 'email_contato', 'cep', 'endereco',
        'numero', 'bairro', 'cidade', 'uf',
        // Redes sociais e contato direto — usados pelo footer da vitrine.
        'whatsapp', 'instagram_url', 'facebook_url', 'tiktok_url',
    ];
}
