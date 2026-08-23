<?php

namespace App\Services;

use App\Models\Carrinho;
use App\Models\Cliente;

class CarrinhoService
{
    public function obterOuCriar(?Cliente $cliente, ?string $sessionId): Carrinho
    {
        if ($cliente) {
            return Carrinho::firstOrCreate(['cliente_id' => $cliente->id]);
        }

        return Carrinho::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Chamado no momento do login: junta o carrinho de visitante (por session_id)
     * com o carrinho salvo da conta, sem perder itens já escolhidos antes de logar.
     */
    public function mesclarAoLogar(Cliente $cliente, ?string $sessionId): Carrinho
    {
        $carrinhoCliente = Carrinho::firstOrCreate(['cliente_id' => $cliente->id]);

        if (! $sessionId) {
            return $carrinhoCliente;
        }

        $carrinhoVisitante = Carrinho::where('session_id', $sessionId)->first();

        if ($carrinhoVisitante) {
            foreach ($carrinhoVisitante->itens as $item) {
                $carrinhoCliente->itens()->create([
                    'produto_id' => $item->produto_id,
                    'variacao_id' => $item->variacao_id,
                    'quantidade' => $item->quantidade,
                ]);
            }
            $carrinhoVisitante->delete();
        }

        return $carrinhoCliente->fresh('itens.produto');
    }
}
