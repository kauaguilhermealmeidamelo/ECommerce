<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id', 'nome', 'slug', 'descricao',
        'preco', 'preco_promocional', 'condicao',
        'estoque', 'ativo', 'destaque',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function variacoes(): HasMany
    {
        return $this->hasMany(ProdutoVariacao::class);
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem');
    }

    // Preço efetivo, considerando promoção
    public function getPrecoAtualAttribute(): string
    {
        return $this->preco_promocional ?? $this->preco;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
