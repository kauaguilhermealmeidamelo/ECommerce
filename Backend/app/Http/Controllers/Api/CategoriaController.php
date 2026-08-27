<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * GET /api/admin/categorias
     * Lista flat (todas as categorias, todos os níveis) — usada no <select>
     * do formulário de produto, onde o lojista escolhe a categoria folha.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => Categoria::orderBy('nome')->get()]);
    }

    /**
     * GET /api/categorias/arvore
     * Retorna só as categorias de topo (sem pai), cada uma com suas
     * filhas carregadas recursivamente — pronto pra montar o menu
     * Categoria > Subcategoria > Sub-subcategoria de uma vez.
     */
    public function arvore(): JsonResponse
    {
        $categorias = Categoria::whereNull('categoria_pai_id')
            ->with('filhasRecursivas')
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $categorias]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categorias,slug'],
            'categoria_pai_id' => ['nullable', 'exists:categorias,id'],
        ]);

        return response()->json(['data' => Categoria::create($dados)], 201);
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:categorias,slug,'.$categoria->id],
            'categoria_pai_id' => ['nullable', 'exists:categorias,id', 'not_in:'.$categoria->id],
        ]);

        $categoria->update($dados);

        return response()->json(['data' => $categoria]);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        // Impede apagar categoria que ainda tem filhas ou produtos —
        // evita órfãos no menu ou produtos sem categoria válida.
        if ($categoria->filhas()->exists()) {
            abort(422, 'Essa categoria tem subcategorias. Remova-as primeiro.');
        }

        if ($categoria->produtos()->exists()) {
            abort(422, 'Essa categoria tem produtos cadastrados. Mova-os antes de remover.');
        }

        $categoria->delete();

        return response()->json(status: 204);
    }
}