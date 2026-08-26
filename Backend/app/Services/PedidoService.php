<?php

namespace App\Services;

use App\Enums\StatusPedido;
use App\Models\Carrinho;
use App\Models\Pedido;

// STUB — substitua pelo seu PedidoService real, se já existia lógica
// aqui (ex: cálculo de descontos, cupons). Os três métodos abaixo
// reúnem tudo que construímos nesta conversa: criação a partir do
// carrinho, gravação do destinatário e confirmação de pagamento.

class PedidoService
{
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
     * Chamado pelo webhook do Mercado Pago assim que o pagamento é aprovado.
     * Idempotente — pode ser chamado mais de uma vez sem debitar estoque em dobro.
     */
    public function confirmarPagamento(Pedido $pedido): void
    {
        if ($pedido->status === StatusPedido::Pago) {
            return;
        }

        $pedido->update(['status' => StatusPedido::Pago->value]);

        foreach ($pedido->itens as $item) {
            if ($item->produto_variacao_id) {
                $item->variacao()->decrement('estoque', $item->quantidade);
            } else {
                $item->produto()->decrement('estoque', $item->quantidade);
            }
        }

        $this->configuracaoLojaService->desativarProdutosSeConfigurado($pedido);
    }
}
