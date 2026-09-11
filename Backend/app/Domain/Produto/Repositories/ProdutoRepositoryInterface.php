<?php

namespace App\Domain\Produtos\Repositories;

use App\Domain\Produtos\Entities\Produto;

interface ProdutoRepositoryInterface
{
    public function obterPorId(int $id): ?Produto;

    public function listarTodos(): array;

    public function salvar(Produto $produto): Produto;

    public function deletar(int $id): bool;
}
