<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['nome' => ['required', 'string', 'max:100']]);

        $categoria = Categoria::create([
            'nome' => $request->nome,
            'slug' => Str::slug($request->nome),
            'ordem' => Categoria::max('ordem') + 1,
        ]);

        return response()->json($categoria, 201);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate(['nome' => ['required', 'string', 'max:100']]);

        $categoria->update(['nome' => $request->nome]);

        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete(); // produtos.categoria_id vira null (nullOnDelete na migration)

        return response()->json(null, 204);
    }
}
