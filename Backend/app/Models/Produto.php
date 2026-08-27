<?php

namespace App\Models;

// STUB — substitua pelo seu model real. Os campos preco_custo, ativo,
// preco_original, max_parcelas e desconto_pix_percentual foram os que
// adicionamos nesta conversa; o restante é a base mínima assumida.

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'preco', 'preco_original', 'preco_custo',
        'estoque', 'ativo', 'categoria_id', 'imagem_url',
        'max_parcelas', 'desconto_pix_percentual',
        'peso_gramas', 'altura_cm', 'largura_cm', 'comprimento_cm',
    ];

    protected $casts = ['ativo' => 'boolean'];

    // Sempre vai junto no JSON — é isso que a lista de admin e o
    // storefront devem exibir, nunca o campo "estoque" cru sozinho
    // (que só faz sentido pra produto sem variação de tamanho).
    protected $appends = ['estoque_total'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function variacoes()
    {
        return $this->hasMany(ProdutoVariacao::class);
    }

    public function variacoesDisponiveis()
    {
        return $this->hasMany(ProdutoVariacao::class)->where('estoque', '>', 0);
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem');
    }

    public function scopeRecentes($query, int $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    /**
     * Estoque "de verdade" do produto: se ele tem variação de tamanho,
     * é a soma do estoque de cada tamanho cadastrado; senão, é o campo
     * estoque direto (peça única, sem variação — ex: item de brechó).
     * Nem a tela de admin nem o storefront precisam decidir qual dos
     * dois campos usar — sempre leem estoque_total.
     */
    public function getEstoqueTotalAttribute(): int
    {
        $variacoes = $this->relationLoaded('variacoes') ? $this->variacoes : $this->variacoes()->get();

        return $variacoes->isNotEmpty() ? (int) $variacoes->sum('estoque') : (int) $this->estoque;
    }
}