<?php

namespace App\Domain\Produto\Repositories;

use App\Domain\Produto\Entities\Produto;

interface ProdutoRepositoryInterface
{
    public function obterPorId(int $id): ?Produto;
    public function listarTodos(?int $categoriaId = null): array;
    public function listarMaisVendidos(int $limite = 8): array;
    public function salvar(Produto $produto): Produto;
    public function deletar(int $id): bool;
}
