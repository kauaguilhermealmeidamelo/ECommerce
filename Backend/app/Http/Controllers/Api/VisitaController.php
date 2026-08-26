<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VisitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function __construct(private readonly VisitaService $visitaService)
    {
    }

    /**
     * POST /api/visitas (público, sem auth — chamado pelo storefront a cada pageview)
     */
    public function registrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'pagina' => ['nullable', 'string', 'max:255'],
        ]);

        $sessaoId = $request->header('X-Session-Id', 'anonimo');

        $this->visitaService->registrar(
            pagina: $dados['pagina'] ?? '/',
            sessaoId: $sessaoId,
            ip: $request->ip(),
        );

        return response()->json(status: 204);
    }

    /**
     * GET /api/admin/visitas/resumo (protegido)
     */
    public function resumo(): JsonResponse
    {
        return response()->json(['data' => $this->visitaService->resumo()]);
    }
}
