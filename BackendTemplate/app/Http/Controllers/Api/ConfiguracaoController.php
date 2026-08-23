<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;

class ConfiguracaoController extends Controller
{
    // Endpoint público consumido pelo frontend para montar nome da loja, cores, whatsapp etc.
    public function index()
    {
        return response()->json(
            Configuracao::all()->pluck('valor', 'chave')
        );
    }
}
