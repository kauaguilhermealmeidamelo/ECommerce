<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCarrinho extends Model
{
    protected $table = 'itens_carrinho';

    protected $fillable = ['carrinho_id', 'produto_id', 'produto_variacao_id', 'quantidade', 'preco_unitario'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
