<?php

namespace App\Domain\Pedido\UseCases;

use App\Models\Carrinho;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use Illuminate\Validation\ValidationException;

class GerenciarCarrinhoUseCase
{
    public function obterCarrinhoAtual(?string $sessaoId, ?int $usuarioId): Carrinho
    {
        if ($usuarioId) {
            return Carrinho::firstOrCreate(['usuario_id' => $usuarioId]);
        }

        if (!$sessaoId) {
            // Sem sessão identificável — gera uma sessão nova em vez de
            // colidir com o carrinho de sessao_id nulo de outro visitante.
            $sessaoId = (string) \Illuminate\Support\Str::uuid();
        }

        return Carrinho::firstOrCreate(['sessao_id' => $sessaoId]);
    }

    public function adicionarItem(Carrinho $carrinho, int $produtoId, int $quantidade, ?string $tamanho = null)
    {
        $produto = Produto::findOrFail($produtoId);
        $variacao = null;

        if ($produto->variacoes()->exists()) {
            if (!$tamanho) {
                throw ValidationException::withMessages(['tamanho' => 'Selecione um tamanho.']);
            }

            $variacao = ProdutoVariacao::where('produto_id', $produtoId)->where('tamanho', $tamanho)->firstOrFail();

            if ($variacao->estoque < $quantidade) {
                throw ValidationException::withMessages(['tamanho' => 'Estoque insuficiente pra esse tamanho.']);
            }
        } elseif ($produto->estoque < $quantidade) {
            throw ValidationException::withMessages(['produto' => 'Estoque insuficiente.']);
        }

        $item = $carrinho->itens()->firstOrNew([
            'produto_id' => $produtoId,
            'produto_variacao_id' => $variacao?->id,
        ]);

        $quantidadeAnterior = $item->exists ? (int) $item->quantidade : 0;

        $item->preco_unitario = $produto->preco;
        $item->quantidade = $quantidadeAnterior + $quantidade;
        $item->save();

        return $item;
    }
}
