<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'API da loja no ar. Use /api/*.']);
});

/*
|--------------------------------------------------------------------------
| Login social (Google) — ficam fora de /api de propósito
|--------------------------------------------------------------------------
| GOOGLE_REDIRECT_URI no .env aponta pra /auth/google/callback (sem
| prefixo /api), então essas rotas precisam viver aqui, não em api.php.
| O AuthController já tinha os métodos prontos, só faltavam as rotas.
*/
Route::get('/auth/google', [AuthController::class, 'redirecionarGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'callbackGoogle']);
