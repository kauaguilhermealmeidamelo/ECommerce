<?php
namespace App\Infrastructure\Http\Resources;

use App\Domain\Produto\Entities\Produto;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Produto $produto */
        $produto = $this->resource;

        return [
            'id' => $produto->id(),
            'nome' => trim($produto->nome()),
            'preco' => $produto->preco()->emReais(),
            'categoria_id' => $produto->categoriaId(),
            'estoque' => $produto->estoque(),
            'ativo' => $produto->ativo(),
        ];
    }
}