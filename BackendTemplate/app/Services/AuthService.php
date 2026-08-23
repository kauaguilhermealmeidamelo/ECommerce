<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteProvedor;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    public function registrar(string $nome, string $email, string $senha): Cliente
    {
        return Cliente::create([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => Hash::make($senha),
        ]);
    }

    public function autenticarComSenha(string $email, string $senha): ?Cliente
    {
        $cliente = Cliente::where('email', $email)->first();

        if (! $cliente || ! $cliente->senha_hash || ! Hash::check($senha, $cliente->senha_hash)) {
            return null;
        }

        return $cliente;
    }

    /**
     * Recebe o id_token vindo do frontend (Google Identity Services),
     * valida com o Google e retorna (ou cria) o Cliente correspondente.
     */
    public function autenticarComGoogle(string $idToken): Cliente
    {
        $googleUser = Socialite::driver('google')->stateless()->userFromToken($idToken);

        $provedor = ClienteProvedor::where('provedor', 'google')
            ->where('provedor_id', $googleUser->getId())
            ->first();

        if ($provedor) {
            return $provedor->cliente;
        }

        // Já existe conta com esse e-mail cadastrada por senha direta?
        // Aqui optamos por vincular automaticamente por e-mail verificado do Google.
        // Alternativa mais rígida: pedir confirmação de senha antes de vincular.
        $cliente = Cliente::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            ['nome' => $googleUser->getName() ?? $googleUser->getEmail()]
        );

        $cliente->provedores()->create([
            'provedor' => 'google',
            'provedor_id' => $googleUser->getId(),
        ]);

        return $cliente;
    }
}
