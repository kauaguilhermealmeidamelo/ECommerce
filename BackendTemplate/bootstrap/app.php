<?php

use App\Http\Middleware\CabecalhosSeguranca;
use App\Http\Middleware\GarantirTokenAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum: necessário para autenticação via SPA (cookie) além do token de API.
        $middleware->statefulApi();

        // Aplica os cabeçalhos de segurança (X-Frame-Options, HSTS, etc.) em toda resposta da API.
        $middleware->api(append: [
            CabecalhosSeguranca::class,
        ]);

        // Alias usado nas rotas /admin/* em routes/api.php — bloqueia token de cliente comum.
        $middleware->alias([
            'admin.token' => GarantirTokenAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
