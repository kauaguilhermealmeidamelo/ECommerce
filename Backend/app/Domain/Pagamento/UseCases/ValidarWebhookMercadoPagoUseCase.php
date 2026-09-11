<?php

namespace App\Domain\Pagamento\UseCases;

use App\Models\ConfiguracaoPagamento;
use Illuminate\Http\Request;

class ValidarWebhookMercadoPagoUseCase
{
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

        $dataId = $request->input('data.id') ?? $request->query('data.id');

        if (!$dataId) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $secret = ConfiguracaoPagamento::atual()->webhookSecret();

        if (!$secret) {
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