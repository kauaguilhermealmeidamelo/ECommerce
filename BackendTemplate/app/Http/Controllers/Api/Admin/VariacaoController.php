<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVariacaoRequest;
use App\Models\Produto;

class VariacaoController extends Controller
{
    public function store(StoreVariacaoRequest $request, Produto $produto)
    {
        $variacao = $produto->variacoes()->create($request->validated());

        return response()->json($variacao, 201);
    }

    public function destroy(Produto $produto, int $variacaoId)
    {
        $produto->variacoes()->findOrFail($variacaoId)->delete();

        return response()->json(null, 204);
    }
}
