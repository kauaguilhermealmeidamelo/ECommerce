<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusPedido;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    /**
     * GET /api/admin/clientes
     * AJUSTE `User` e a relação `pedidos()` se seu model de cliente tiver
     * outro nome (ex: Usuario) ou a FK não for usuario_id.
     */
    public function index(): JsonResponse
    {
        $clientes = User::where('is_admin', false)
            ->withCount('pedidos')
            ->withSum(['pedidos as total_gasto' => fn ($q) => $q->where('status', StatusPedido::Pago->value)], 'total')
            ->orderByDesc('pedidos_count')
            ->get()
            ->map(fn (User $usuario) => [
                'id' => $usuario->id,
                'nome' => $usuario->name,
                'email' => $usuario->email,
                'telefone' => $usuario->telefone ?? null,
                'total_pedidos' => $usuario->pedidos_count,
                'total_gasto' => (float) ($usuario->total_gasto ?? 0),
                'desde' => $usuario->created_at,
            ]);

        return response()->json(['data' => $clientes]);
    }
}
