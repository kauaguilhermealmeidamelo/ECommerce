<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CabecalhosSeguranca
{
    // Cabeçalhos de resposta que fecham brechas comuns e baratas de corrigir.
    // Aplicar globalmente em bootstrap/app.php (Laravel 11+) ou no Kernel (Laravel 10).
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY'); // evita clickjacking no checkout/admin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS: só ativar depois de confirmar que o domínio tem HTTPS estável,
        // porque força o navegador a nunca mais tentar HTTP nesse domínio.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
