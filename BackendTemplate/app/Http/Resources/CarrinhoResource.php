<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarrinhoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'itens' => $this->itens->map(fn ($item) => [
                'id' => $item->id,
                'produto' => $item->produto->nome,
                'quantidade' => $item->quantidade,
                'preco_unitario' => (float) $item->produto->preco_atual,
                'subtotal' => (float) $item->produto->preco_atual * $item->quantidade,
            ]),
            'subtotal' => $this->subtotal(),
        ];
    }
}
