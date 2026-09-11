<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoModel extends Model
{
    protected $table = 'produtos';

    // Whitelist estrita bloqueando Mass Assignment indesejado
    protected $fillable = ['nome', 'descricao', 'preco', 'categoria_id', 'estoque', 'ativo'];

    protected $casts = [
        'ativo' => 'boolean',
        'preco' => 'decimal:2',
    ];
}