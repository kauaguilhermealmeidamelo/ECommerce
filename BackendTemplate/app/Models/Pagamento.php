<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = [
        'pedido_id', 'mp_payment_id', 'status',
        'metodo_pagamento', 'valor', 'payload_json', 'recebido_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'payload_json' => 'array',
        'recebido_em' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
}
