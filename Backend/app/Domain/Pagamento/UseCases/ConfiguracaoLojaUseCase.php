<?php

namespace App\Domain\Pagamento\UseCases;

use App\Models\ConfiguracaoLoja;
use App\Models\Pedido;

class ConfiguracaoLojaUseCase
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