<?php

namespace App\Domain\Produto\UseCases;

use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;

class ExcluirProdutoUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(int $id): void
    {
        $this->produtoRepository->deletar($id);
    }
}
