<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoVariacao extends Model
{
    protected $table = 'produto_variacoes';

    protected $fillable = ['produto_id', 'tamanho', 'cor', 'sku', 'estoque'];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
