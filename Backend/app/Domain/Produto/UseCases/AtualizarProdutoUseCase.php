<?php

namespace App\Domain\Produto\UseCases;

use App\Domain\Produto\Entities\Produto;
use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;

class AtualizarProdutoUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(int $id, array $dados): Produto
    {
        $produto = (new ObterProdutoUseCase($this->produtoRepository))->executar($id);

        foreach ([
            'nome', 'preco', 'precoCusto', 'estoque', 'ativo', 'categoriaId',
            'peso', 'altura', 'largura', 'comprimento', 'descricao', 'variacoes', 'imagens',
        ] as $campo) {
            if (array_key_exists($campo, $dados)) {
                $produto->{$campo} = $dados[$campo];
            }
        }

        return $this->produtoRepository->salvar($produto);
    }
}
