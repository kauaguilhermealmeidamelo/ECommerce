<?php

namespace App\Models;

// STUB — substitua pelo seu CarrinhoService/model real (com a lógica de
// merge guest→autenticado via X-Session-Id que você já tinha construído).

use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    protected $fillable = ['usuario_id', 'sessao_id'];

    public function itens()
    {
        return $this->hasMany(ItemCarrinho::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->itens->sum(fn ($item) => $item->quantidade * $item->preco_unitario);
    }
}
