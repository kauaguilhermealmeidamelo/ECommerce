<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnderecoRequest;
use Illuminate\Http\Request;

class EnderecoController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->enderecos;
    }

    public function store(EnderecoRequest $request)
    {
        $endereco = $request->user()->enderecos()->create($request->validated());

        return response()->json($endereco, 201);
    }

    public function update(EnderecoRequest $request, int $id)
    {
        // Escopo pelo próprio usuário autenticado — nunca aceitar id de endereço de outro cliente.
        $endereco = $request->user()->enderecos()->findOrFail($id);
        $endereco->update($request->validated());

        return response()->json($endereco);
    }

    public function destroy(Request $request, int $id)
    {
        $endereco = $request->user()->enderecos()->findOrFail($id);
        $endereco->delete();

        return response()->json(null, 204);
    }
}
