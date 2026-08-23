<?php

namespace App\Models;

use App\Enums\StatusPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_id', 'endereco_id', 'cupom_id', 'status',
        'total', 'frete', 'mercadopago_preference_id', 'mercadopago_payment_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'frete' => 'decimal:2',
        'status' => StatusPedido::class,
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }
}
