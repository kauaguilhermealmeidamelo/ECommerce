<?php

namespace App\Services;

use App\Models\Pedido;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Cria uma Preference (Checkout Pro) a partir de um pedido já persistido.
     * Retorna o array de dados da preference criada no Mercado Pago.
     */
    public function criarPreference(Pedido $pedido): array
    {
        $items = $pedido->itens->map(fn ($item) => [
            'title' => $item->produto->nome,
            'quantity' => $item->quantidade,
            'unit_price' => (float) $item->preco_unitario,
            'currency_id' => 'BRL',
        ])->toArray();

        $client = new PreferenceClient();

        $preference = $client->create([
            'items' => $items,
            'external_reference' => (string) $pedido->id,
            'back_urls' => [
                'success' => config('app.frontend_url').'/pedido/sucesso',
                'failure' => config('app.frontend_url').'/pedido/falha',
                'pending' => config('app.frontend_url').'/pedido/pendente',
            ],
            'auto_return' => 'approved',
            'notification_url' => config('app.url').'/api/webhooks/mercadopago',
        ]);

        $pedido->update(['mercadopago_preference_id' => $preference->id]);

        return [
            'preference_id' => $preference->id,
            'init_point' => $preference->init_point,
        ];
    }

    /**
     * Consulta o pagamento direto na API do Mercado Pago.
     * NUNCA confiar apenas no corpo do webhook — sempre reconsultar aqui.
     */
    public function consultarPagamento(string $paymentId): array
    {
        $client = new PaymentClient();
        $payment = $client->get($paymentId);

        return [
            'id' => $payment->id,
            'status' => $payment->status, // approved, pending, rejected...
            'metodo_pagamento' => $payment->payment_method_id,
            'valor' => $payment->transaction_amount,
            'external_reference' => $payment->external_reference, // id do nosso Pedido
        ];
    }
}
