<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function criarPreferencia(Pedido $pedido): array
    {
        $client = new PreferenceClient();

        $itens = $pedido->itens->map(fn ($item) => [
            'title' => $item->produto->nome,
            'quantity' => $item->quantidade,
            'unit_price' => (float) $item->preco_unitario,
        ])->toArray();

        $preferencia = $client->create([
            'items' => $itens,
            'external_reference' => (string) $pedido->id,
            'notification_url' => config('app.url').'/api/webhooks/mercadopago',
        ]);

        return ['init_point' => $preferencia->init_point];
    }

    /**
     * Consulta um pagamento real na API do Mercado Pago pelo ID recebido
     * no webhook (`data.id`). Retorna array vazio em caso de falha —
     * quem chama trata isso como "não confirmar o pedido ainda".
     */
    public function consultarPagamento(string $pagamentoId): array
    {
        $client = new PaymentClient();

        try {
            $pagamento = $client->get((int) $pagamentoId);
        } catch (MPApiException $e) {
            Log::warning('Falha ao consultar pagamento no Mercado Pago', [
                'pagamento_id' => $pagamentoId,
                'status' => $e->getApiResponse()?->getStatusCode(),
                'resposta' => $e->getApiResponse()?->getContent(),
            ]);

            return [];
        }

        return [
            'status' => $pagamento->status,
            'external_reference' => $pagamento->external_reference,
            'transaction_amount' => $pagamento->transaction_amount,
        ];
    }
}

