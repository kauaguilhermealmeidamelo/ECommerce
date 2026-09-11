<?php
namespace App\Domain\Shared\ValueObjects;

final class Money
{
    private function __construct(private readonly int $centavos) {}

    public static function fromReais(float $valor): self
    {
        if ($valor < 0) {
            throw new \InvalidArgumentException('Valor monetário não pode ser negativo.');
        }
        return new self((int) round($valor * 100));
    }

    public static function fromCentavos(int $centavos): self
    {
        return new self($centavos);
    }

    public function emReais(): float { return $this->centavos / 100; }
    public function emCentavos(): int { return $this->centavos; }
}