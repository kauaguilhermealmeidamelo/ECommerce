<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Sem cadastro público de propósito: administrador é criado via seeder/tinker
    // pelo próprio desenvolvedor, não pela API.
    public function login(AdminLoginRequest $request)
    {
        $admin = Administrador::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->senha, $admin->senha_hash)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        // Token com ability própria — usado no middleware das rotas /api/admin/*
        $token = $admin->createToken('admin', ['admin'])->plainTextToken;

        return response()->json(['administrador' => $admin, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada.']);
    }
}
