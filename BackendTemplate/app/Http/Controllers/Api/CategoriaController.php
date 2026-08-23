<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::orderBy('ordem')->get(['id', 'nome', 'slug']);
    }
}
