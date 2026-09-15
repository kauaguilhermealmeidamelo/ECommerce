<?php

use App\Infrastructure\Http\Controllers\Api\AuthController;
use App\Infrastructure\Http\Controllers\Api\CarrinhoController;
use App\Infrastructure\Http\Controllers\Api\CategoriaController;
use App\Infrastructure\Http\Controllers\Api\CheckoutController;
use App\Infrastructure\Http\Controllers\Api\ClienteController;
use App\Infrastructure\Http\Controllers\Api\ClientePedidoController;
use App\Infrastructure\Http\Controllers\Api\ConfiguracaoLojaController;
use App\Infrastructure\Http\Controllers\Api\ConfiguracaoPagamentoController;
use App\Infrastructure\Http\Controllers\Api\ConfiguracaoSegurancaController;
use App\Infrastructure\Http\Controllers\Api\DashboardController;
use App\Infrastructure\Http\Controllers\Api\EntregaController;
use App\Infrastructure\Http\Controllers\Api\EnvioController;
use App\Infrastructure\Http\Controllers\Api\InformacaoLojaController;
use App\Infrastructure\Http\Controllers\Api\PedidoController;
use App\Infrastructure\Http\Controllers\Api\ProdutoController;
use App\Infrastructure\Http\Controllers\Api\VisitaController;
use App\Infrastructure\Http\Controllers\Api\WebhookMercadoPagoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticação (Padrão e Socialite)
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/registro', [AuthController::class, 'registrar'])->middleware('throttle:6,1');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| Rotas públicas — storefront (sem autenticação)
|--------------------------------------------------------------------------
*/
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/mais-vendidos', [ProdutoController::class, 'maisVendidos']);
Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);

Route::get('/categorias/arvore', [CategoriaController::class, 'arvore']);

Route::get('/carrinho', [CarrinhoController::class, 'mostrar']);
Route::post('/carrinho/itens', [CarrinhoController::class, 'adicionarItem']);
Route::patch('/carrinho/itens/{item}', [CarrinhoController::class, 'atualizarItem']);
Route::delete('/carrinho/itens/{item}', [CarrinhoController::class, 'removerItem']);

Route::post('/checkout/frete', [CheckoutController::class, 'calcularFreteEndpoint']);
Route::post('/checkout/finalizar', [CheckoutController::class, 'finalizar']);

Route::post('/visitas', [VisitaController::class, 'registrar']);
Route::get('/frete/opcoes', [EntregaController::class, 'opcoes']);

Route::get('/loja', [InformacaoLojaController::class, 'mostrar']);

Route::post('/webhooks/mercadopago', [WebhookMercadoPagoController::class, 'processar']);

/*
|--------------------------------------------------------------------------
| Rotas autenticadas globais (Cliente e Admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Rotas do cliente autenticado — vitrine (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('minha-conta')->group(function () {
    Route::get('/pedidos', [ClientePedidoController::class, 'index']);
    Route::get('/pedidos/{pedido}', [ClientePedidoController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Rotas protegidas — painel administrativo
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/produtos', [ProdutoController::class, 'index']);
    Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);
    Route::apiResource('produtos', ProdutoController::class)->except(['index', 'show']);

    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categorias/arvore', [CategoriaController::class, 'arvore']);
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy']);

    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/categorias', [DashboardController::class, 'categorias']);

    Route::get('/visitas/resumo', [VisitaController::class, 'resumo']);

    Route::get('/entregas/configuracao', [EntregaController::class, 'mostrar']);
    Route::put('/entregas/configuracao', [EntregaController::class, 'atualizar']);

    Route::get('/envios/pendentes', [EnvioController::class, 'pendentes']);
    Route::patch('/envios/{pedido}/marcar-enviado', [EnvioController::class, 'marcarEnviado']);

    Route::get('/clientes', [ClienteController::class, 'index']);

    Route::get('/loja', [InformacaoLojaController::class, 'mostrar']);
    Route::put('/loja', [InformacaoLojaController::class, 'atualizar']);
    Route::post('/loja', [InformacaoLojaController::class, 'atualizar']);

    Route::get('/configuracoes-loja', [ConfiguracaoLojaController::class, 'mostrar']);
    Route::put('/configuracoes-loja', [ConfiguracaoLojaController::class, 'atualizar']);

    Route::get('/configuracoes-pagamento', [ConfiguracaoPagamentoController::class, 'mostrar']);
    Route::put('/configuracoes-pagamento', [ConfiguracaoPagamentoController::class, 'atualizar']);

    Route::get('/configuracoes-seguranca', [ConfiguracaoSegurancaController::class, 'mostrar']);
    Route::put('/configuracoes-seguranca', [ConfiguracaoSegurancaController::class, 'atualizar']);
});
