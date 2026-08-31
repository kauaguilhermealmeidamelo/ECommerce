<?php

namespace App\Http\Middleware;

use App\Models\ConfiguracaoSeguranca;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModoManutencao
{
    /**
     * Aplicado só no grupo de rotas PÚBLICAS do storefront (routes/api.php).
     * As rotas /api/admin/* nunca passam por aqui, então o painel continua
     * acessível pro lojista mesmo com a loja "fechada" pro público.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (ConfiguracaoSeguranca::atual()->modo_manutencao) {
            return response()->json([
                'message' => 'Loja em manutenção. Voltamos em breve.',
            ], 503);
        }

        return $next($request);
    }
}
