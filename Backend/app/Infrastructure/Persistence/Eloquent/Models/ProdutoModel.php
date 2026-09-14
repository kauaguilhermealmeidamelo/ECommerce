<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoModel extends Model
{
    protected $table = 'produtos';

    // Whitelist estrita bloqueando Mass Assignment indesejado
    protected $fillable = [
        'nome', 'descricao', 'preco', 'preco_custo', 'categoria_id', 'estoque', 'ativo',
        'peso_gramas', 'altura_cm', 'largura_cm', 'comprimento_cm',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'preco' => 'decimal:2',
        'preco_custo' => 'decimal:2',
    ];
}