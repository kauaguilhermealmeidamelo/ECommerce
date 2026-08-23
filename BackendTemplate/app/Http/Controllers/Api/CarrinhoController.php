<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrinhoResource;
use App\Services\CarrinhoService;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function __construct(private CarrinhoService $carrinhoService) {}

    public function show(Request $request)
    {
        $carrinho = $this->carrinhoService->obterOuCriar(
            $request->user(),
            $request->header('X-Session-Id'),
        );

        return new CarrinhoResource($carrinho->load('itens.produto'));
    }

    public function adicionarItem(Request $request)
    {
        $request->validate([
            'produto_id' => ['required', 'exists:produtos,id'],
            'variacao_id' => ['nullable', 'exists:produto_variacoes,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
        ]);

        $carrinho = $this->carrinhoService->obterOuCriar(
            $request->user(),
            $request->header('X-Session-Id'),
        );

        $carrinho->itens()->create($request->only(['produto_id', 'variacao_id', 'quantidade']));

        return new CarrinhoResource($carrinho->load('itens.produto'));
    }

    public function removerItem(Request $request, int $itemId)
    {
        $carrinho = $this->carrinhoService->obterOuCriar(
            $request->user(),
            $request->header('X-Session-Id'),
        );

        $carrinho->itens()->where('id', $itemId)->delete();

        return new CarrinhoResource($carrinho->load('itens.produto'));
    }
}
