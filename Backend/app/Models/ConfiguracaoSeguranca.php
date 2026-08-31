<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSeguranca extends Model
{
    protected $table = 'configuracoes_seguranca';

    protected $fillable = [
        'notificacoes_email',
        'autenticacao_dois_fatores',
        'modo_manutencao',
    ];

    protected $casts = [
        'notificacoes_email' => 'boolean',
        'autenticacao_dois_fatores' => 'boolean',
        'modo_manutencao' => 'boolean',
    ];

    /**
     * Sempre use este helper em vez de ::first() direto — garante que
     * a linha singleton exista (com os padrões seguros da migration)
     * mesmo antes de o admin salvar qualquer coisa em Configurações.
     */
    public static function atual(): self
    {
        return static::first() ?? static::create();
    }
}
