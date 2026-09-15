<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Infrastructure\Http\Middleware\AdminApenas::class,
            // Resolve o usuário autenticado (Sanctum) quando um Bearer token
            // é enviado, mas NÃO bloqueia a rota se não houver token — usado
            // nas rotas públicas de carrinho/checkout, que precisam saber se
            // existe um cliente logado (pra usar o carrinho por usuario_id em
            // vez do carrinho por sessao_id de visitante).
            'cliente.opcional' => \App\Infrastructure\Http\Middleware\TentativaAutenticacaoSanctum::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Força respostas JSON para qualquer rota /api/* sem redirecionar
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();