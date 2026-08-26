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

        return response()->json(['data' => $carrinho->fresh('itens.produto')]);
    }
}
