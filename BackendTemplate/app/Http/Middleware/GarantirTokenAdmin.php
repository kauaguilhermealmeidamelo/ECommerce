<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GarantirTokenAdmin
{
    // Bloqueia qualquer token que não tenha sido emitido pelo Admin\AuthController
    // com a ability 'admin' — impede que um token de cliente comum acesse rotas de publicação.
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (! $token || ! $token->can('admin')) {
            return response()->json(['message' => 'Acesso restrito ao administrador da loja.'], 403);
        }

        return $next($request);
    }
}
