<?php

namespace App\Services;

use App\Enums\StatusPedido;
use App\Models\Carrinho;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;

// STUB — substitua pelo seu PedidoService real, se já existia lógica
// aqui (ex: cálculo de descontos, cupons). Os métodos abaixo reúnem tudo
// que construímos nesta conversa: criação a partir do carrinho, gravação
// do destinatário e o tratamento completo dos status de pagamento do
// Mercado Pago (aprovado, recusado, em análise, estornado).

class PedidoService
{
    /**
     * payment_type_id do MP → rótulo curto usado no painel admin.
     * 'bank_transfer' é o valor histórico do Pix; contas antigas do MP
     * ainda podem mandar assim, então cobrimos os dois.
     */
    private const METODOS_PAGAMENTO = [
        'pix' => 'pix',
        'bank_transfer' => 'pix',
        'credit_card' => 'credit_card',
        'debit_card' => 'debit_card',
        'ticket' => 'boleto',
        'account_money' => 'saldo_mp',
    ];

    public function __construct(private readonly ConfiguracaoLojaService $configuracaoLojaService)
    {
    }

    public function criarAPartirDoCarrinho(Carrinho $carrinho, array $dadosEntrega, float $valorFrete): Pedido
    {
        $pedido = Pedido::create([
            'usuario_id' => $carrinho->usuario_id,
            'status' => StatusPedido::Pendente->value,
            'total' => $carrinho->subtotal + $valorFrete,
            'valor_frete' => $valorFrete,
        ]);

        foreach ($carrinho->itens as $itemCarrinho) {
            $pedido->itens()->create([
                'produto_id' => $itemCarrinho->produto_id,
                'produto_variacao_id' => $itemCarrinho->produto_variacao_id,
                'tamanho' => $itemCarrinho->variacao?->tamanho,
                'quantidade' => $itemCarrinho->quantidade,
                'preco_unitario' => $itemCarrinho->preco_unitario,
            ]);
        }

        $this->gravarDestinatario($pedido, $dadosEntrega);

        return $pedido;
    }

    private function gravarDestinatario(Pedido $pedido, array $dadosEntrega): void
    {
        if (($dadosEntrega['metodo_entrega'] ?? null) === 'retirada') {
            $pedido->update(['metodo_entrega' => 'retirada', 'valor_frete' => 0]);
            return;
        }

        $pedido->update([
            'metodo_entrega' => $dadosEntrega['metodo_entrega'],
            'destinatario_nome' => $dadosEntrega['nome'] ?? null,
            'destinatario_cep' => $dadosEntrega['cep'] ?? null,
            'destinatario_endereco' => $dadosEntrega['endereco'] ?? null,
            'destinatario_numero' => $dadosEntrega['numero'] ?? null,
            'destinatario_complemento' => $dadosEntrega['complemento'] ?? null,
            'destinatario_bairro' => $dadosEntrega['bairro'] ?? null,
            'destinatario_cidade' => $dadosEntrega['cidade'] ?? null,
            'destinatario_uf' => $dadosEntrega['uf'] ?? null,
        ]);
    }

    /**
     * Chamado pelo WebhookMercadoPagoController pra QUALQUER notificação
     * de pagamento — não só aprovação. Roteia pro status certo do pedido
     * e é seguro de chamar várias vezes com o mesmo payload (o MP reenvia
     * notificações; idempotência é obrigatória aqui).
     *
     * $pagamento é o array retornado por MercadoPagoService::consultarPagamento().
     */
    public function atualizarComPagamento(Pedido $pedido, array $pagamento): void
    {
        // Já processamos esse pagamento exato nesse mesmo status —
        // reenvio do MP, não faz nada de novo (evita debitar estoque
        // duas vezes, reenviar e-mail duas vezes quando isso existir etc).
        if (
            $pedido->mercadopago_payment_id === $pagamento['id']
            && $pedido->status->value === $this->statusParaPedido($pagamento['status'])->value
        ) {
            return;
        }

        $statusAnterior = $pedido->status;
        $metodo = self::METODOS_PAGAMENTO[$pagamento['payment_type_id'] ?? ''] ?? $pagamento['payment_type_id'] ?? null;

        match ($pagamento['status']) {
            'approved' => $this->aprovar($pedido, $pagamento, $metodo),
            'refunded', 'charged_back' => $this->estornar($pedido, $pagamento, $statusAnterior),
            'rejected' => $pedido->update([
                'status' => StatusPedido::Recusado->value,
                'mercadopago_payment_id' => $pagamento['id'],
                'metodo_pagamento' => $metodo,
                'motivo_recusa' => $pagamento['status_detail'] ?? null,
            ]),
            'cancelled' => $pedido->update([
                'status' => StatusPedido::Cancelado->value,
                'mercadopago_payment_id' => $pagamento['id'],
                'metodo_pagamento' => $metodo,
            ]),
            'in_process', 'in_mediation', 'pending' => $pedido->update([
                'status' => StatusPedido::EmAnalise->value,
                'mercadopago_payment_id' => $pagamento['id'],
                'metodo_pagamento' => $metodo,
            ]),
            default => Log::info('Status de pagamento do MP não mapeado, ignorado', [
                'pedido_id' => $pedido->id,
                'status' => $pagamento['status'],
            ]),
        };
    }

    /**
     * Idempotente — pode ser chamado mais de uma vez sem debitar estoque
     * em dobro (a checagem de mercadopago_payment_id em
     * atualizarComPagamento já filtra a maior parte dos reenvios, isso
     * aqui é uma segunda trava caso o payment_id mude por algum motivo).
     */
    private function aprovar(Pedido $pedido, array $pagamento, ?string $metodo): void
    {
        if ($pedido->status === StatusPedido::Pago) {
            return;
        }

        $pedido->update([
            'status' => StatusPedido::Pago->value,
            'mercadopago_payment_id' => $pagamento['id'],
            'metodo_pagamento' => $metodo,
            'pago_em' => now(),
            'motivo_recusa' => null,
        ]);

        foreach ($pedido->itens as $item) {
            if ($item->produto_variacao_id) {
                $item->variacao()->decrement('estoque', $item->quantidade);
            } else {
                $item->produto()->decrement('estoque', $item->quantidade);
            }
        }

        $this->configuracaoLojaService->desativarProdutosSeConfigurado($pedido);
    }

    /**
     * Estorno/chargeback depois de um pagamento já aprovado: devolve o
     * estoque debitado. Se o pedido nunca chegou a ficar "pago" (caso
     * raro, mas possível), não mexe em estoque — não havia o que devolver.
     */
    private function estornar(Pedido $pedido, array $pagamento, StatusPedido $statusAnterior): void
    {
        if ($statusAnterior === StatusPedido::Estornado) {
            return;
        }

        $pedido->update([
            'status' => StatusPedido::Estornado->value,
            'mercadopago_payment_id' => $pagamento['id'],
        ]);

        if ($statusAnterior !== StatusPedido::Pago && $statusAnterior !== StatusPedido::Concluido) {
            return;
        }

        foreach ($pedido->itens as $item) {
            if ($item->produto_variacao_id) {
                $item->variacao()->increment('estoque', $item->quantidade);
            } else {
                $item->produto()->increment('estoque', $item->quantidade);
            }
        }

        Log::info('Pedido estornado — estoque devolvido', ['pedido_id' => $pedido->id]);
    }

    private function statusParaPedido(string $statusMp): StatusPedido
    {
        return match ($statusMp) {
            'approved' => StatusPedido::Pago,
            'refunded', 'charged_back' => StatusPedido::Estornado,
            'rejected' => StatusPedido::Recusado,
            'cancelled' => StatusPedido::Cancelado,
            'in_process', 'in_mediation', 'pending' => StatusPedido::EmAnalise,
            default => StatusPedido::Pendente,
        };
    }
}
