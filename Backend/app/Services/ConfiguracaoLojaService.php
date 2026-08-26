<?php

namespace App\Services;

use App\Models\ConfiguracaoLoja;
use App\Models\Pedido;

class ConfiguracaoLojaService
{
    public function obter(): ConfiguracaoLoja
    {
        return ConfiguracaoLoja::first() ?? ConfiguracaoLoja::create();
    }

    public function atualizar(array $dados): ConfiguracaoLoja
    {
        $config = $this->obter();
        $config->update($dados);

        return $config;
    }

    /**
     * Chamado pelo PedidoService assim que um pedido é confirmado como pago.
     * Se a config estiver ligada, cada produto comprado sai de venda.
     */
    public function desativarProdutosSeConfigurado(Pedido $pedido): void
    {
        if (!$this->obter()->produto_expira_apos_venda) {
            return;
        }

        foreach ($pedido->itens as $item) {
            $item->produto()->update(['ativo' => false]);
        }
    }
}
