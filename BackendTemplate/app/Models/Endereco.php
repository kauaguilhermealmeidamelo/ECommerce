<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    protected $fillable = [
        'cliente_id', 'apelido', 'cep', 'logradouro',
        'numero', 'complemento', 'bairro', 'cidade', 'estado', 'padrao',
    ];

    protected $casts = ['padrao' => 'boolean'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
