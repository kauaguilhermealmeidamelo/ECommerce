<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Cupom;
use App\Services\CarrinhoService;
use App\Services\MercadoPagoService;
use App\Services\PedidoService;

class CheckoutController extends Controller
{
    public function __construct(
        private CarrinhoService $carrinhoService,
        private PedidoService $pedidoService,
        private MercadoPagoService $mercadoPagoService,
    ) {}

    public function store(StoreCheckoutRequest $request)
    {
        $carrinho = $this->carrinhoService->obterOuCriar(
            $request->user(),
            $request->header('X-Session-Id'),
        );

        if ($carrinho->itens->isEmpty()) {
            return response()->json(['message' => 'Carrinho vazio.'], 422);
        }

        $cupomId = null;
        if ($request->cupom_codigo) {
            $cupom = Cupom::where('codigo', $request->cupom_codigo)->first();
            $cupomId = $cupom && $cupom->valido() ? $cupom->id : null;
        }

        $pedido = $this->pedidoService->criarAPartirDoCarrinho(
            $carrinho,
            $request->endereco_id,
            $cupomId,
        );

        $preference = $this->mercadoPagoService->criarPreference($pedido);

        return response()->json([
            'pedido_id' => $pedido->id,
            'init_point' => $preference['init_point'],
        ]);
    }
}
