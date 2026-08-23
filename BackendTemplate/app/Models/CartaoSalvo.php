<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartaoSalvo extends Model
{
    protected $table = 'cartoes_salvos';

    // Nunca adicionar campos de número/cvv/validade aqui — apenas os tokens do Mercado Pago.
    protected $fillable = [
        'cliente_id', 'mp_customer_id', 'mp_card_id',
        'bandeira', 'ultimos_digitos', 'padrao',
    ];

    protected $casts = ['padrao' => 'boolean'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
