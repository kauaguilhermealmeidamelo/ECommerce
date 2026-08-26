<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    /**
     * GET /api/dashboard
     * Retorna resumo do mês atual, mês anterior, série dos últimos 6 meses
     * e ranking de categorias mais vendidas (últimos 90 dias).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->resumoGeral(),
        ]);
    }

    /**
     * GET /api/dashboard/categorias?dias=30&limite=5
     * Endpoint separado caso queira um filtro de período independente na UI.
     */
    public function categorias(): JsonResponse
    {
        $dias = (int) request()->query('dias', 90);
        $limite = (int) request()->query('limite', 10);

        return response()->json([
            'data' => $this->dashboardService->categoriasMaisVendidas($dias, $limite),
        ]);
    }
}
