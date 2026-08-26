<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApenas
{
    /**
     * AJUSTE: se seu model User/Usuario ainda não tem campo de papel,
     * adicione uma coluna `is_admin` (boolean) ou `papel` (enum) e
     * troque a condição abaixo. Isso é o que efetivamente protege o
     * painel — o path escondido no frontend é só uma camada extra.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Acesso restrito a administradores.');
        }

        return $next($request);
    }
}
