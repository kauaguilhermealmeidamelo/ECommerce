<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrinho extends Model
{
    protected $fillable = ['cliente_id', 'session_id'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(CarrinhoItem::class);
    }

    public function subtotal(): float
    {
        return $this->itens->sum(function (CarrinhoItem $item) {
            return $item->quantidade * $item->produto->preco_atual;
        });
    }
}
