<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientePedidoController extends Controller
{
    /**
     * GET /api/minha-conta/pedidos
     * Só os pedidos do usuário autenticado — nunca lê usuario_id da query,
     * sempre do token (auth:sanctum).
     */
    public function index(Request $request): JsonResponse
    {
        $pedidos = Pedido::with('itens.produto')
            ->where('usuario_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'pagina_atual' => $pedidos->currentPage(),
                'ultima_pagina' => $pedidos->lastPage(),
                'total' => $pedidos->total(),
            ],
        ]);
    }

    /**
     * GET /api/minha-conta/pedidos/{pedido}
     * Traz itens + status de envio (codigo_rastreio/transportadora/enviado_em)
     * pra tela de rastreio. 403 se o pedido pertencer a outro cliente —
     * o Laravel já resolve o model pelo ID da URL, então a checagem de
     * dono é obrigatória aqui, nunca implícita.
     */
    public function show(Request $request, Pedido $pedido): JsonResponse
    {
        if ($pedido->usuario_id !== $request->user()->id) {
            abort(403, 'Esse pedido não pertence à sua conta.');
        }

        return response()->json(['data' => $pedido->load('itens.produto')]);
    }
}
