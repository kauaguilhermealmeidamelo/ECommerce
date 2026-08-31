<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CarrinhoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function __construct(private readonly CarrinhoService $carrinhoService)
    {
    }

    public function mostrar(Request $request): JsonResponse
    {
        $carrinho = $this->carrinhoService->obterCarrinhoAtual(
            $request->header('X-Session-Id'),
            $request->user()?->id
        );

        return response()->json(['data' => $carrinho->load('itens.produto', 'itens.variacao')]);
    }

    public function adicionarItem(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'produto_id' => ['required', 'exists:produtos,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'tamanho' => ['nullable', 'string'],
        ]);

        $carrinho = $this->carrinhoService->obterCarrinhoAtual(
            $request->header('X-Session-Id'),
            $request->user()?->id
        );

        $this->carrinhoService->adicionarItem($carrinho, $dados['produto_id'], $dados['quantidade'], $dados['tamanho'] ?? null);

        return response()->json(['data' => $carrinho->fresh('itens.produto', 'itens.variacao')]);
    }

    /**
     * PATCH /api/carrinho/itens/{item}
     * Troca a quantidade de um item já no carrinho (usado pelos botões
     * +/- da tela de carrinho). findOrFail via $carrinho->itens() garante
     * que ninguém edita item de carrinho alheio trocando o ID na URL.
     */
    public function atualizarItem(Request $request, int $item): JsonResponse
    {
        $dados = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
        ]);

        $carrinho = $this->carrinhoService->obterCarrinhoAtual(
            $request->header('X-Session-Id'),
            $request->user()?->id
        );

        $itemCarrinho = $carrinho->itens()->findOrFail($item);
        $itemCarrinho->update(['quantidade' => $dados['quantidade']]);

        return response()->json(['data' => $carrinho->fresh('itens.produto', 'itens.variacao')]);
    }

    /**
     * DELETE /api/carrinho/itens/{item}
     */
    public function removerItem(Request $request, int $item): JsonResponse
    {
        $carrinho = $this->carrinhoService->obterCarrinhoAtual(
            $request->header('X-Session-Id'),
            $request->user()?->id
        );

        $carrinho->itens()->where('id', $item)->delete();

        return response()->json(['data' => $carrinho->fresh('itens.produto', 'itens.variacao')]);
    }
}