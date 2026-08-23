<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => (float) $this->total,
            'frete' => (float) $this->frete,
            'itens' => $this->whenLoaded('itens', fn () => $this->itens->map(fn ($item) => [
                'produto' => $item->produto->nome,
                'quantidade' => $item->quantidade,
                'preco_unitario' => (float) $item->preco_unitario,
            ])),
            'criado_em' => $this->created_at,
        ];
    }
}
