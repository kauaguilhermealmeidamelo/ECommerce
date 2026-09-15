<?php

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TentativaAutenticacaoSanctum
{
    /**
     * Se vier um Bearer token, troca o guard padrão pra 'sanctum' só
     * nesta request — assim $request->user() passa a resolver o cliente
     * logado nas rotas públicas de carrinho/checkout. Sem token, segue
     * null normalmente e o fluxo de visitante (sessao_id) continua
     * funcionando sem exigir login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            Auth::shouldUse('sanctum');
        }

        return $next($request);
    }
}