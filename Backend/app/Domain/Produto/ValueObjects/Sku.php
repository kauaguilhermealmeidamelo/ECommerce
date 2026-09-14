<?php

namespace App\Domain\Produto\ValueObjects;

use InvalidArgumentException;

class Sku
{
    private string $codigo;

    public function __construct(string $codigo)
    {
        $codigoLimpo = trim(strtoupper($codigo));

        if (empty($codigoLimpo)) {
            throw new InvalidArgumentException("O SKU não pode estar vazio.");
        }

        // Valida se possui um formato alfanumérico seguro (letras, números, hífens e underscores)
        if (!preg_match('/^[A-Z0-9\-_]+$/', $codigoLimpo)) {
            throw new InvalidArgumentException("O SKU informado possui caracteres inválidos.");
        }

        $this->codigo = $codigoLimpo;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function __toString(): string
    {
        return $this->codigo;
    }
}