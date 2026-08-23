<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $produtos = Produto::ativos()
            ->with(['categoria', 'imagens', 'variacoes'])
            ->when($request->categoria, fn ($q) => $q->whereHas('categoria', fn ($c) => $c->where('slug', $request->categoria)))
            ->when($request->condicao, fn ($q) => $q->where('condicao', $request->condicao))
            ->when($request->tamanho, fn ($q) => $q->whereHas('variacoes', fn ($v) => $v->where('tamanho', $request->tamanho)))
            ->paginate(20);

        return ProdutoResource::collection($produtos);
    }

    public function show(string $slug)
    {
        $produto = Produto::ativos()
            ->with(['categoria', 'imagens', 'variacoes'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProdutoResource($produto);
    }
}
