<?php

namespace App\Domain\Produto\Entities;

class Produto
{
    public function __construct(
        public ?int $id = null,
        public string $nome = '',
        public float $preco = 0.0,
        public ?float $precoCusto = null,
        public int $estoque = 0,
        public ?bool $ativo = true,
        public ?int $categoriaId = null,
        public ?float $peso = null,
        public ?float $altura = null,
        public ?float $largura = null,
        public ?float $comprimento = null,
        public ?string $categoria = 'Geral',
        public ?string $sku = null,
        public ?string $descricao = null,
        public array $variacoes = [],
        public array $imagens = []
    ) {}
}
