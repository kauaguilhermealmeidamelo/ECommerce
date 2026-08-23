<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrinhoItem extends Model
{
    protected $table = 'carrinho_itens';

    protected $fillable = ['carrinho_id', 'produto_id', 'variacao_id', 'quantidade'];

    public function carrinho(): BelongsTo
    {
        return $this->belongsTo(Carrinho::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function variacao(): BelongsTo
    {
        return $this->belongsTo(ProdutoVariacao::class, 'variacao_id');
    }
}
