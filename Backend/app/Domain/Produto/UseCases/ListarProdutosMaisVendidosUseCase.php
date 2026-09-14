<?php

namespace App\Domain\Produto\UseCases;

use App\Domain\Produto\Entities\Produto;
use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;

class ListarProdutosMaisVendidosUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    /** @return Produto[] */
    public function executar(int $limite = 8): array
    {
        return $this->produtoRepository->listarMaisVendidos($limite);
    }
}
