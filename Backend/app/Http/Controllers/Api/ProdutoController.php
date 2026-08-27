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
    public function index(Request $request): JsonResponse
    {
        $query = Produto::with(['categoria', 'variacoes']);

        if (!$request->user()?->is_admin) {
            $query->where('ativo', true);
        }

        return response()->json(['data' => $query->paginate(20)->items()]);
    }

    public function show(Request $request, Produto $produto): JsonResponse
    {
        if (!$produto->ativo && !$request->user()?->is_admin) {
            abort(404);
        }

        return response()->json(['data' => $produto->load(['categoria', 'variacoes'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'preco' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'estoque' => ['nullable', 'integer', 'min:0'],
            'descricao' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => Produto::create($dados)], 201);
    }

    public function update(Request $request, Produto $produto): JsonResponse
    {
        // Antes: $produto->update($request->all()) — sem validação, sem
        // checagem de tipo/existência. Alinhado com o store() para que a
        // tela de edição não quebre com dados inválidos e para impedir
        // salvar uma categoria_id inexistente, preço negativo, etc.
        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'preco' => ['sometimes', 'required', 'numeric', 'min:0'],
            'categoria_id' => ['sometimes', 'required', 'exists:categorias,id'],
            'estoque' => ['nullable', 'integer', 'min:0'],
            'descricao' => ['nullable', 'string'],
        ]);

        $produto->update($dados);

        return response()->json(['data' => $produto]);
    }

    public function destroy(Produto $produto): JsonResponse
    {
        $produto->delete();

        return response()->json(status: 204);
    }
}
