<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'preco' => (float) $this->preco,
            'preco_promocional' => $this->preco_promocional ? (float) $this->preco_promocional : null,
            'condicao' => $this->condicao,
            'em_estoque' => $this->estoque > 0,
            'categoria' => $this->whenLoaded('categoria', fn () => $this->categoria->nome),
            'imagens' => $this->whenLoaded('imagens', fn () => $this->imagens->pluck('url')),
            'variacoes' => $this->whenLoaded('variacoes', fn () => $this->variacoes->map(fn ($v) => [
                'id' => $v->id,
                'tamanho' => $v->tamanho,
                'cor' => $v->cor,
                'estoque' => $v->estoque,
            ])),
        ];
    }
}
