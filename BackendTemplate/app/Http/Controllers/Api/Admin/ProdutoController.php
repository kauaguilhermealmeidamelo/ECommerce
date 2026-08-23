<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;

class ProdutoController extends Controller
{
    // Lista todos, inclusive inativos/rascunho — diferente do ProdutoController público.
    public function index()
    {
        $produtos = Produto::with(['categoria', 'imagens', 'variacoes'])
            ->latest()
            ->paginate(20);

        return ProdutoResource::collection($produtos);
    }

    public function store(StoreProdutoRequest $request)
    {
        $produto = Produto::create($request->validated() + ['slug' => $request->slug]);

        return new ProdutoResource($produto);
    }

    public function show(Produto $produto)
    {
        return new ProdutoResource($produto->load(['categoria', 'imagens', 'variacoes']));
    }

    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $produto->update($request->validated());

        return new ProdutoResource($produto->fresh(['categoria', 'imagens', 'variacoes']));
    }

    // Soft "despublicar": em vez de apagar, desativa — preserva histórico em itens_pedido antigos.
    public function destroy(Produto $produto)
    {
        $produto->update(['ativo' => false]);

        return response()->json(['message' => 'Produto despublicado.']);
    }
}
