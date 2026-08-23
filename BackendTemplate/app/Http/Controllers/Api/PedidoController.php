<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    // Usado pela tela de retorno do checkout, pra mostrar o status atualizado ao cliente.
    public function show(Request $request, int $id)
    {
        $pedido = $request->user()
            ? $request->user()->pedidos()->with('itens.produto')->findOrFail($id)
            : abort(404);

        return new PedidoResource($pedido);
    }

    public function index(Request $request)
    {
        return PedidoResource::collection(
            $request->user()->pedidos()->with('itens.produto')->latest()->paginate(10)
        );
    }
}
