<?php

namespace App\Http\Controllers\Api;

// STUB — substitua pelo seu AuthService/AuthController real (Sanctum +
// Socialite/Google já decididos anteriormente).

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $usuario = User::where('email', $dados['email'])->first();

        if (!$usuario || !Hash::check($dados['senha'], $usuario->password)) {
            throw ValidationException::withMessages(['email' => 'Credenciais inválidas.']);
        }

        $token = $usuario->createToken('painel-admin')->plainTextToken;

        return response()->json(['token' => $token, 'usuario' => $usuario]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }
}
