<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->cartoesSalvos;
    }

    /**
     * O frontend tokeniza o cartão com o SDK.js do Mercado Pago no navegador
     * e manda pra cá apenas o card_id resultante — nunca o número real do cartão.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mp_customer_id' => ['required', 'string'],
            'mp_card_id' => ['required', 'string'],
            'bandeira' => ['nullable', 'string'],
            'ultimos_digitos' => ['nullable', 'string', 'size:4'],
        ]);

        $cartao = $request->user()->cartoesSalvos()->create($request->only([
            'mp_customer_id', 'mp_card_id', 'bandeira', 'ultimos_digitos',
        ]));

        return response()->json($cartao, 201);
    }

    public function destroy(Request $request, int $id)
    {
        $cartao = $request->user()->cartoesSalvos()->findOrFail($id);
        $cartao->delete();

        return response()->json(null, 204);
    }
}
