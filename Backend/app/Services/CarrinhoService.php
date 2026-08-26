<?php

namespace App\Services;

// STUB — substitua pelo seu CarrinhoService real (que já tinha a lógica
// de merge guest→autenticado). O método adicionarItem já está integrado
// com produto_variacao_id, conforme construímos na conversa.

use App\Models\Carrinho;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarrinhoService
{
    public function obterCarrinhoAtual(?string $sessaoId, ?int $usuarioId): Carrinho
    {
        if ($usuarioId) {
            return Carrinho::firstOrCreate(['usuario_id' => $usuarioId]);
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

        return $carrinho->itens()->updateOrCreate(
            ['produto_id' => $produtoId, 'produto_variacao_id' => $variacao?->id],
            ['preco_unitario' => $produto->preco]
        )->increment('quantidade', $quantidade);
    }
}
