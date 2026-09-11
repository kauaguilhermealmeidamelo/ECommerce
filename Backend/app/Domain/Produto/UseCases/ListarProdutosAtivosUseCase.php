<?php

namespace App\Domain\Produtos\UseCases;

use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;

class ListarProdutosUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(): array
    {
        return $this->produtoRepository->listarTodos();
    }
}