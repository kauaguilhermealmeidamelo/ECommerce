<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPedido extends Model
{
    protected $table = 'itens_pedido';

    protected $fillable = ['pedido_id', 'produto_id', 'variacao_id', 'quantidade', 'preco_unitario'];

    protected $casts = ['preco_unitario' => 'decimal:2'];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
