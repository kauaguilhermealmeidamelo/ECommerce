<?php

namespace App\Infrastructure\Http\Controllers\Api;

use App\Infrastructure\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     * Sempre cria com is_admin=false.
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

    /**
     * GET /api/auth/me (protegido — auth:sanctum)
     * Retorna o usuário autenticado a partir do token. Usado pela tela
     * de callback do login social (Google), que só recebe o token na URL
     * e precisa buscar os dados do usuário pra popular a store no frontend.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['usuario' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }

    public function redirectToGoogle()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->stateless()->redirect();
    }

    // Alias de segurança caso alguma rota antiga chame em português
    public function redirecionarGoogle()
    {
        return $this->redirectToGoogle();
    }

    public function handleGoogleCallback()
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
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

            return redirect()->away("{$frontendUrl}/auth/callback?token={$token}");
        } catch (\Exception $e) {
            Log::error('Erro no callback do Google', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Falha na autenticação com o Google'], 500);
        }
    }

    // Alias de segurança caso alguma rota antiga chame em português
    public function callbackGoogle()
    {
        return $this->handleGoogleCallback();
    }
}