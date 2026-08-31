<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoSeguranca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfiguracaoSegurancaController extends Controller
{
    /**
     * GET /api/admin/configuracoes-seguranca
     * Protegido por ['auth:sanctum','admin'] nas rotas — só quem já está
     * logado como admin consegue ver ou mudar esses interruptores.
     */
    public function mostrar(): JsonResponse
    {
        return response()->json(['data' => ConfiguracaoSeguranca::atual()]);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'notificacoes_email' => ['required', 'boolean'],
            'autenticacao_dois_fatores' => ['required', 'boolean'],
            'modo_manutencao' => ['required', 'boolean'],
        ]);

        $config = ConfiguracaoSeguranca::atual();
        $ativandoDoisFatores = $dados['autenticacao_dois_fatores'] && !$config->autenticacao_dois_fatores;

        $config->update($dados);

        // Não é um erro do usuário — é um aviso operacional: se o MAIL_*
        // não estiver configurado, o próximo login de admin vai falhar
        // em enviar o código e ninguém consegue entrar no painel.
        if ($ativandoDoisFatores && !config('mail.default')) {
            Log::warning('Autenticação em 2 fatores ativada sem driver de e-mail configurado (MAIL_MAILER no .env).');
        }

        return response()->json(['data' => $config]);
    }
}
