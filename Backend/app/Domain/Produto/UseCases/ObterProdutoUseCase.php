<?php

namespace App\Domain\Produto\UseCases;

use App\Domain\Produto\Entities\Produto;
use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;
use RuntimeException;

class ObterProdutoUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(int $id): Produto
    {
        $produto = $this->produtoRepository->obterPorId($id);

        if (!$produto) {
            throw new RuntimeException('Produto não encontrado.');
        }

        return $produto;
    }
}
