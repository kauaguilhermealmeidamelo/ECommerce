<?php

namespace App\Infrastructure\Http\Controllers\Api;

use App\Infrastructure\Http\Controllers\Controller;
use App\Models\InformacaoLoja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InformacaoLojaController extends Controller
{
    /**
     * GET /api/loja (público, storefront/footer) e GET /api/admin/loja
     */
    public function mostrar(): JsonResponse
    {
        $info = InformacaoLoja::first() ?? InformacaoLoja::create(['nome' => 'Minha Loja']);

        return response()->json(['data' => $info]);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'image', 'max:10240'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email_contato' => ['nullable', 'email', 'max:255'],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'max:2'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
        ]);

        $info = InformacaoLoja::first() ?? new InformacaoLoja();
        unset($dados['logo'], $dados['banner']);

        foreach (['logo' => 'logo_url', 'banner' => 'banner_url'] as $campo => $coluna) {
            if ($request->hasFile($campo)) {
                if ($info->{$coluna}) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $info->{$coluna}));
                }
                $dados[$coluna] = url(Storage::url($request->file($campo)->store('loja', 'public')));
            }
        }

        $info->fill($dados);
        $info->save();

        return response()->json(['data' => $info]);
    }
}
