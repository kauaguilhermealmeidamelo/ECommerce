<?php

namespace App\Domain\Produtos\UseCases;

use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;

class AtualizarEstoqueUseCase
{
    public function __construct(
        private ProdutoRepositoryInterface $produtoRepository
    ) {}

    public function executar(int $produtoId, int $quantidade, string $operacao = 'adicionar', ?string $nomeVariacao = null): void
    {
        // Correção aqui: removido o "$this->produtoRepository" duplicado
        $produto = $this->produtoRepository->obterPorId($produtoId);

        if (!$produto) {
            throw new RuntimeException("Produto não encontrado.");
        }

        if ($nomeVariacao !== null) {
            $encontrado = false;
            foreach ($produto->variacoes as &$variacao) {
                if (strcasecmp($variacao['nome'], $nomeVariacao) === 0) {
                    $encontrado = true;

                    $novoEstoqueVar = match ($operacao) {
                        'adicionar' => $variacao['estoque'] + $quantidade,
                        'remover' => $variacao['estoque'] - $quantidade,
                        'definir' => $quantidade,
                        default => throw new InvalidArgumentException("Operação de estoque inválida.")
                    };

                    if ($novoEstoqueVar < 0) {
                        throw new InvalidArgumentException("Estoque insuficiente para a variação '{$nomeVariacao}'.");
                    }

                    $variacao['estoque'] = $novoEstoqueVar;
                    break;
                }
            }

            if (!$encontrado) {
                throw new RuntimeException("Variação '{$nomeVariacao}' não encontrada para este produto.");
            }
        } else {
            $novoEstoque = match ($operacao) {
                'adicionar' => $produto->estoque + $quantidade,
                'remover' => $produto->estoque - $quantidade,
                'definir' => $quantidade,
                default => throw new InvalidArgumentException("Operação de estoque inválida.")
            };

            if ($novoEstoque < 0) {
                throw new InvalidArgumentException("Estoque insuficiente.");
            }

            $produto->estoque = $novoEstoque;
        }

        $this->produtoRepository->salvar($produto);
    }
}
