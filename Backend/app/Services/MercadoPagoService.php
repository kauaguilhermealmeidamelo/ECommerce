<?php

namespace App\Services;

use App\Models\ConfiguracaoPagamento;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    /**
     * NÃO configura o token no __construct de propósito — se fizesse
     * isso uma vez só, um admin que troca o token em Configurações só
     * veria o efeito depois de reiniciar o processo PHP (fpm/queue
     * worker). Cada chamada pública busca a credencial atual do banco.
     */
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

    /**
     * Consulta um pagamento real na API do Mercado Pago pelo ID recebido
     * no webhook (`data.id`). Retorna array vazio em caso de falha —
     * quem chama trata isso como "não confirmar o pedido ainda".
     *
     * payment_type_id vem como 'credit_card', 'debit_card', 'ticket'
     * (boleto), 'bank_transfer' (pix), 'account_money' etc — usado pra
     * preencher pedidos.metodo_pagamento (ver PedidoService).
     */
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
