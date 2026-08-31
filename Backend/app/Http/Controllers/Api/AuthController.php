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

    /**
     * POST /api/auth/registro (público — cadastro do cliente na vitrine).
     * Sempre cria com is_admin=false: essa rota nunca deve poder criar
     * um administrador, mesmo que o payload tente forçar o campo.
     */
    public function registrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'senha' => ['required', 'string', 'min:6', 'confirmed'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $usuario = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['senha']),
            'telefone' => $dados['telefone'] ?? null,
            'is_admin' => false,
        ]);

        $token = $usuario->createToken('cliente')->plainTextToken;

        return response()->json(['token' => $token, 'usuario' => $usuario], 201);
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
