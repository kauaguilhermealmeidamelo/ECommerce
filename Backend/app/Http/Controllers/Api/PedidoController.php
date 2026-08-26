<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Pedido::with('itens.produto')->latest()->paginate(20)->items(),
        ]);
    }

    public function show(Pedido $pedido): JsonResponse
    {
        return response()->json(['data' => $pedido->load('itens.produto', 'usuario')]);
    }
}
