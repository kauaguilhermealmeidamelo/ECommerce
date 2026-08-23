<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cupom extends Model
{
    
    protected $table = 'cupons';
    
    protected $fillable = ['codigo', 'tipo', 'valor', 'validade', 'usos_max', 'usos_atual'];

    protected $casts = ['validade' => 'date'];

    public function valido(): bool
    {
        if ($this->validade && $this->validade->isPast()) {
            return false;
        }

        if ($this->usos_max !== null && $this->usos_atual >= $this->usos_max) {
            return false;
        }

        return true;
    }

    public function calcularDesconto(float $subtotal): float
    {
        return $this->tipo === 'percentual'
            ? round($subtotal * ($this->valor / 100), 2)
            : min($this->valor, $subtotal);
    }
}
