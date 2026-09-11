<?php

namespace App\Domain\Produtos\ValueObjects;

use InvalidArgumentException;

class Preco
{
    private float $valor;

    public function __construct(float $valor)
    {
        if ($valor < 0) {
            throw new InvalidArgumentException("O preço não pode ser negativo.");
        }
        
        // Garante precisão decimal de 2 casas
        $this->valor = round($valor, 2);
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function calcularDiferencaPercentual(Preco $outroPreco): float
    {
        if ($outroPreco->getValor() <= 0) {
            return 0.0;
        }
        return (($this->valor - $outroPreco->getValor()) / $outroPreco->getValor()) * 100;
    }
}