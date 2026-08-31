<?php

namespace App\Services;

use App\Models\ConfiguracaoPagamento;
use Illuminate\Http\Request;

class MercadoPagoWebhookValidator
{
    /**
     * Valida o header x-signature conforme documentação oficial do Mercado Pago.
     * O secret vem de: Painel MP > Suas integrações > (sua aplicação) > Webhooks > Chave secreta.
     * Salvo criptografado em configuracoes_pagamento (Configurações > Pagamento no
     * admin), com fallback pra MERCADOPAGO_WEBHOOK_SECRET no .env.
     */
    public function valido(Request $request): bool
    {
        $assinatura = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (!$assinatura || !$requestId) {
            return false;
        }

        [$ts, $hashRecebido] = $this->extrairPartes($assinatura);

        if (!$ts || !$hashRecebido) {
            return false;
        }

        // data.id pode vir tanto no body quanto na query string, dependendo do
        // tipo de notificação — o MP recomenda checar os dois.
        $dataId = $request->input('data.id') ?? $request->query('data.id');

        if (!$dataId) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $secret = ConfiguracaoPagamento::atual()->webhookSecret();

        if (!$secret) {
            // Sem secret configurado, não dá pra validar — falha fechado,
            // nunca aberto. Melhor perder um webhook do que aceitar um forjado.
            return false;
        }

        $hashCalculado = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($hashCalculado, $hashRecebido);
    }

    private function extrairPartes(string $assinatura): array
    {
        $partes = [];

        foreach (explode(',', $assinatura) as $parte) {
            [$chave, $valor] = array_pad(explode('=', trim($parte), 2), 2, null);
            if ($chave && $valor) {
                $partes[trim($chave)] = trim($valor);
            }
        }

        return [$partes['ts'] ?? null, $partes['v1'] ?? null];
    }
}
