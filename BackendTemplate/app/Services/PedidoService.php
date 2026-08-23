<?php

namespace App\Services;

use App\Enums\StatusPedido;
use App\Models\Carrinho;
use App\Models\Pagamento;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    public function criarAPartirDoCarrinho(Carrinho $carrinho, ?int $enderecoId, ?int $cupomId): Pedido
    {
        return DB::transaction(function () use ($carrinho, $enderecoId, $cupomId) {
            $total = 0;

            $pedido = Pedido::create([
                'cliente_id' => $carrinho->cliente_id,
                'endereco_id' => $enderecoId,
                'cupom_id' => $cupomId,
                'status' => StatusPedido::AguardandoPagamento,
                'total' => 0, // atualizado depois de somar os itens
            ]);

            foreach ($carrinho->itens as $item) {
                // Sempre revalida preço e estoque direto no banco — nunca confia no que veio do front.
                $precoAtual = $item->produto->preco_atual;
                $total += $precoAtual * $item->quantidade;

                $pedido->itens()->create([
                    'produto_id' => $item->produto_id,
                    'variacao_id' => $item->variacao_id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $precoAtual,
                ]);
            }

            $pedido->update(['total' => $total]);

            return $pedido->fresh('itens.produto');
        });
    }

    /**
     * Chamado pelo WebhookController depois de confirmar o pagamento direto na API do MP.
     * Idempotente: se o mp_payment_id já foi processado, não duplica efeito.
     */
    public function confirmarPagamento(array $dadosPagamento): void
    {
        if (Pagamento::where('mp_payment_id', $dadosPagamento['id'])->exists()) {
            return; // já processado, webhook duplicado
        }

        $pedido = Pedido::findOrFail($dadosPagamento['external_reference']);

        Pagamento::create([
            'pedido_id' => $pedido->id,
            'mp_payment_id' => $dadosPagamento['id'],
            'status' => $dadosPagamento['status'],
            'metodo_pagamento' => $dadosPagamento['metodo_pagamento'],
            'valor' => $dadosPagamento['valor'],
            'payload_json' => $dadosPagamento,
            'recebido_em' => now(),
        ]);

        if ($dadosPagamento['status'] === 'approved') {
            $pedido->update([
                'status' => StatusPedido::Pago,
                'mercadopago_payment_id' => $dadosPagamento['id'],
            ]);
        }
    }
}
