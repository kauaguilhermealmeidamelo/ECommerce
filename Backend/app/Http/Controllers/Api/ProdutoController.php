<?php

namespace App\Http\Controllers\Api;

// STUB — CRUD mínimo. Substitua pelo seu ProdutoController real (com
// Form Requests e API Resources, conforme já construído anteriormente).

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Produto::with(['categoria', 'variacoes'])->paginate(20)->items()]);
    }

    public function show(Produto $produto): JsonResponse
    {
        return response()->json(['data' => $produto->load(['categoria', 'variacoes'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'preco' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'estoque' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(['data' => Produto::create($dados)], 201);
    }

    public function update(Request $request, Produto $produto): JsonResponse
    {
        $produto->update($request->all());

        return response()->json(['data' => $produto]);
    }

    public function destroy(Produto $produto): JsonResponse
    {
        $produto->delete();

        return response()->json(status: 204);
    }
}
