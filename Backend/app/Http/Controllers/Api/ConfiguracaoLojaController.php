<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConfiguracaoLojaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracaoLojaController extends Controller
{
    public function __construct(private readonly ConfiguracaoLojaService $configuracaoLojaService)
    {
    }

    public function mostrar(): JsonResponse
    {
        return response()->json(['data' => $this->configuracaoLojaService->obter()]);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'produto_expira_apos_venda' => ['required', 'boolean'],
        ]);

        $config = $this->configuracaoLojaService->atualizar($dados);

        return response()->json(['data' => $config]);
    }
}