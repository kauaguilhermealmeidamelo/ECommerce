<?php

namespace App\Domain\Produto\UseCases;

use App\Domain\Produto\Entities\Produto;
use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;
use App\Domain\Produto\ValueObjects\Preco;
use App\Domain\Produto\ValueObjects\Sku;
use InvalidArgumentException;

class CriarProdutoUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(array $dados): Produto
    {
        // Validação e aplicação dos Value Objects de Domínio
        $preco = new Preco($dados['preco']);
        $precoCusto = isset($dados['preco_custo']) ? new Preco($dados['preco_custo']) : null;

        $produto = new Produto(
            id: null,
            nome: $dados['nome'],
            descricao: $dados['descricao'] ?? '',
            preco: $preco->getValor(),
            precoCusto: $precoCusto?->getValor(),
            estoque: $dados['estoque'] ?? 0,
            ativo: $dados['ativo'] ?? true,
            categoriaId: $dados['categoria_id'] ?? null,
            peso: $dados['peso'] ?? null,
            altura: $dados['altura'] ?? null,
            largura: $dados['largura'] ?? null,
            comprimento: $dados['comprimento'] ?? null,
            variacoes: $dados['variacoes'] ?? [],
            imagens: $dados['imagens'] ?? []
        );

        return $this->produtoRepository->salvar($produto);
    }
}
