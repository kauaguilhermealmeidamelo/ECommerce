<?php

namespace App\Models;

use App\Enums\StatusPedido;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'usuario_id', 'status', 'total', 'metodo_entrega', 'valor_frete',
        'codigo_rastreio', 'transportadora', 'enviado_em',
        'destinatario_nome', 'destinatario_cep', 'destinatario_endereco',
        'destinatario_numero', 'destinatario_complemento', 'destinatario_bairro',
        'destinatario_cidade', 'destinatario_uf',
        'mercadopago_payment_id', 'metodo_pagamento', 'pago_em', 'motivo_recusa',
    ];

    protected $casts = [
        'status' => StatusPedido::class,
        'enviado_em' => 'datetime',
        'pago_em' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }
}
