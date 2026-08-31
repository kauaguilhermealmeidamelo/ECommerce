<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoPagamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ConfiguracaoPagamentoController extends Controller
{
    /**
     * GET /api/admin/configuracoes-pagamento
     * Nunca devolve o token/segredo em si — só se já está configurado
     * (booleano), igual o padrão de "transportadora_conectada" em
     * EntregaService::obterConfiguracao(). O admin só volta a ver o
     * valor de novo se ele mesmo colar de novo no formulário.
     */
    public function mostrar(): JsonResponse
    {
        $config = ConfiguracaoPagamento::atual();

        return response()->json([
            'data' => [
                'access_token_configurado' => (bool) $config->accessToken(),
                'webhook_secret_configurado' => (bool) $config->webhookSecret(),
            ],
        ]);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'access_token' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $config = ConfiguracaoPagamento::atual();

        // Só regrava se um valor novo foi de fato enviado — reenviar o
        // form com o campo em branco (porque o valor real nunca volta
        // pro frontend) não deve apagar a credencial já salva.
        if (!empty($dados['access_token'])) {
            $config->mercadopago_access_token = Crypt::encryptString($dados['access_token']);
        }

        if (!empty($dados['webhook_secret'])) {
            $config->mercadopago_webhook_secret = Crypt::encryptString($dados['webhook_secret']);
        }

        $config->save();

        return response()->json([
            'data' => [
                'access_token_configurado' => (bool) $config->accessToken(),
                'webhook_secret_configurado' => (bool) $config->webhookSecret(),
            ],
        ]);
    }
}
