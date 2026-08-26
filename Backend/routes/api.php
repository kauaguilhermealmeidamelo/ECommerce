<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrinhoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ConfiguracaoLojaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EntregaController;
use App\Http\Controllers\Api\EnvioController;
use App\Http\Controllers\Api\InformacaoLojaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\VisitaController;
use App\Http\Controllers\Api\WebhookMercadoPagoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas — storefront (sem autenticação)
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);

Route::get('/carrinho', [CarrinhoController::class, 'mostrar']);
Route::post('/carrinho/itens', [CarrinhoController::class, 'adicionarItem']);

Route::post('/checkout/frete', [CheckoutController::class, 'calcularFreteEndpoint']);
Route::post('/checkout/finalizar', [CheckoutController::class, 'finalizar']);

Route::post('/visitas', [VisitaController::class, 'registrar']);
Route::get('/frete/opcoes', [EntregaController::class, 'opcoes']);

Route::post('/webhooks/mercadopago', [WebhookMercadoPagoController::class, 'processar']);

/*
|--------------------------------------------------------------------------
| Rotas protegidas — painel administrativo
|--------------------------------------------------------------------------
| auth:sanctum garante que só usuários autenticados acessam; admin garante
| que só usuários com is_admin=true acessam (ver AdminApenas).
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // index/show precisam ser registrados aqui também: a listagem pública
    // em /api/produtos não deve mostrar produtos inativos (ativo=false),
    // mas o painel admin precisa ver todos os produtos cadastrados.
    Route::get('/produtos', [ProdutoController::class, 'index']);
    Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);
    Route::apiResource('produtos', ProdutoController::class)->except(['index', 'show']);

    // Necessário para o <select> de categoria no formulário de produto.
    Route::get('/categorias', [CategoriaController::class, 'index']);

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

    Route::get('/configuracoes-loja', [ConfiguracaoLojaController::class, 'mostrar']);
    Route::put('/configuracoes-loja', [ConfiguracaoLojaController::class, 'atualizar']);
});