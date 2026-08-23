<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoriaController as AdminCategoriaController;
use App\Http\Controllers\Api\Admin\ProdutoController as AdminProdutoController;
use App\Http\Controllers\Api\Admin\ProdutoImagemController;
use App\Http\Controllers\Api\Admin\VariacaoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrinhoController;
use App\Http\Controllers\Api\CartaoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ConfiguracaoController;
use App\Http\Controllers\Api\EnderecoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

// --- Público: catálogo e configuração da loja ---
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{slug}', [ProdutoController::class, 'show']);
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/configuracoes', [ConfiguracaoController::class, 'index']);

// --- Autenticação ---
// Throttle apertado: alvo clássico de força bruta e credential stuffing.
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/auth/registro', [AuthController::class, 'registro']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'google']);
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
});

// --- Carrinho: funciona pra visitante (X-Session-Id) e cliente logado ---
Route::get('/carrinho', [CarrinhoController::class, 'show']);
Route::post('/carrinho/itens', [CarrinhoController::class, 'adicionarItem']);
Route::delete('/carrinho/itens/{itemId}', [CarrinhoController::class, 'removerItem']);

// --- Webhook: chamado pelo Mercado Pago, nunca pelo frontend ---
// Throttle generoso (o MP pode reenviar em rajada), mas ainda protegido —
// a validação real de quem pode chamar essa rota é a assinatura x-signature, não o rate limit.
Route::middleware('throttle:60,1')->post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);

// --- Protegido: exige token Sanctum válido ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('enderecos', EnderecoController::class)->except(['show']);

    Route::get('/cartoes', [CartaoController::class, 'index']);
    Route::post('/cartoes', [CartaoController::class, 'store']);
    Route::delete('/cartoes/{id}', [CartaoController::class, 'destroy']);

    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
});

// --- Administração da loja: publicação de produto, fotos, categorias ---
// Login separado do cliente (já protegido por throttle no grupo de autenticação acima).
// As rotas abaixo exigem token com ability 'admin' (emitido só pelo Admin\AuthController),
// então um token de cliente comum não passa.
Route::middleware(['auth:sanctum', 'admin.token'])->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);

    Route::apiResource('produtos', AdminProdutoController::class);
    Route::post('/produtos/{produto}/imagens', [ProdutoImagemController::class, 'store']);
    Route::delete('/produtos/{produto}/imagens/{imagemId}', [ProdutoImagemController::class, 'destroy']);
    Route::post('/produtos/{produto}/variacoes', [VariacaoController::class, 'store']);
    Route::delete('/produtos/{produto}/variacoes/{variacaoId}', [VariacaoController::class, 'destroy']);

    Route::post('/categorias', [AdminCategoriaController::class, 'store']);
    Route::put('/categorias/{categoria}', [AdminCategoriaController::class, 'update']);
    Route::delete('/categorias/{categoria}', [AdminCategoriaController::class, 'destroy']);
});
