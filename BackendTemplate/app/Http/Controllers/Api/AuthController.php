<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistroRequest;
use App\Services\AuthService;
use App\Services\CarrinhoService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private CarrinhoService $carrinhoService,
    ) {}

    public function registro(RegistroRequest $request)
    {
        $cliente = $this->authService->registrar(
            $request->nome,
            $request->email,
            $request->senha,
        );

        $token = $cliente->createToken('api')->plainTextToken;

        $this->carrinhoService->mesclarAoLogar($cliente, $request->header('X-Session-Id'));

        return response()->json(['cliente' => $cliente, 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        $cliente = $this->authService->autenticarComSenha($request->email, $request->senha);

        if (! $cliente) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $token = $cliente->createToken('api')->plainTextToken;

        $this->carrinhoService->mesclarAoLogar($cliente, $request->header('X-Session-Id'));

        return response()->json(['cliente' => $cliente, 'token' => $token]);
    }

    public function google(GoogleLoginRequest $request)
    {
        $cliente = $this->authService->autenticarComGoogle($request->id_token);

        $token = $cliente->createToken('api')->plainTextToken;

        $this->carrinhoService->mesclarAoLogar($cliente, $request->header('X-Session-Id'));

        return response()->json(['cliente' => $cliente, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
