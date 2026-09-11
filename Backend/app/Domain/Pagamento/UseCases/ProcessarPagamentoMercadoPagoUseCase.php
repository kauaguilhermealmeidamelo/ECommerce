<?php

namespace App\Domain\Pagamento\UseCases;

use App\Models\ConfiguracaoPagamento;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class ProcessarPagamentoMercadoPagoUseCase
{
    private function configurarCredenciais(): void
    {
        $token = ConfiguracaoPagamento::atual()->accessToken();

        if (!$token) {
            throw new \RuntimeException(
                'Nenhum Access Token do Mercado Pago configurado. Configure em Configurações > Pagamento, ou defina MERCADOPAGO_ACCESS_TOKEN no .env.'
            );
        }

        MercadoPagoConfig::setAccessToken($token);
    }

    public function criarPreferencia(Pedido $pedido): array
    {
        $this->configurarCredenciais();

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

    public function consultarPagamento(string $pagamentoId): array
    {
        try {
            $this->configurarCredenciais();
        } catch (\RuntimeException $e) {
            Log::error($e->getMessage());
            return [];
        }

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
            'id' => (string) $pagamento->id,
            'status' => $pagamento->status,
            'status_detail' => $pagamento->status_detail,
            'external_reference' => $pagamento->external_reference,
            'transaction_amount' => $pagamento->transaction_amount,
            'payment_type_id' => $pagamento->payment_type_id,
        ];
    }
}