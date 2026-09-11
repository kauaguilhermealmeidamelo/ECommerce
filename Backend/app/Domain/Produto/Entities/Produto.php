<?php

namespace App\Domain\Produtos\Entities;

class Produto
{
    public function __construct(
        public ?int $id,
        public string $nome,
        public string $descricao,
        public float $preco,
        public ?float $precoCusto,
        public int $estoque, // Estoque geral ou soma das variações
        public bool $ativo,
        public ?int $categoriaId,
        public ?float $peso,
        public ?float $altura,
        public ?float $largura,
        public ?float $comprimento,
        public array $variacoes = [], // Cada variação possui id, nome (tamanho/cor), preco, estoque
        public array $imagens = []
    ) {}

    public function temEstoqueDisponivelParaTamanho(string $tamanho, int $quantidadeDesejada = 1): bool
    {
        foreach ($this->variacoes as $variacao) {
            // Verifica se o nome/tamanho corresponde e se há estoque suficiente
            if (strcasecmp($variacao['nome'], $tamanho) === 0) {
                return $variacao['estoque'] >= $quantidadeDesejada;
            }
        }
        return false;
    }

    public function deveAparecerNoSite(): bool
    {
        // Se o produto estiver inativo, não aparece
        if (!$this->ativo) {
            return false;
        }

        // Se tiver variações, precisa ter pelo menos uma variação com estoque > 0
        if (!empty($this->variacoes)) {
            foreach ($this->variacoes as $variacao) {
                if ($variacao['estoque'] > 0) {
                    return true;
                }
            }
            return false; // Se todas as variações estiverem zeradas, o produto não aparece/indisponível
        }

        // Sem variações, vale o estoque geral
        return $this->estoque > 0;
    }
}
