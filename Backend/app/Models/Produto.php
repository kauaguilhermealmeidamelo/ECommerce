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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function variacoes()
    {
        return $this->hasMany(ProdutoVariacao::class);
    }
}
