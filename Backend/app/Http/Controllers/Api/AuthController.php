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
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

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

    public function redirecionarGoogle()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');
        
        return $driver->stateless()->redirect();
    }

    public function callbackGoogle()
    {
        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(Str::random(24)),
                ]
            );

            $token = $user->createToken('auth_token')->plainTextToken;
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            return redirect()->away("{$frontendUrl}/auth/callback?token={$token}");

        } catch (\Exception $e) {
            return response()->json(['error' => 'Falha na autenticação com o Google'], 500);
        }
    }
}
