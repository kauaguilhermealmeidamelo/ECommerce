<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * GET /api/admin/categorias
     * Usado no <select> do formulário de produto. Sem isso, não havia
     * como escolher uma categoria válida ao cadastrar um produto.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => Categoria::orderBy('nome')->get()]);
    }
}
