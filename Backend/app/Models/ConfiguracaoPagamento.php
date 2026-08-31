<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ConfiguracaoPagamento extends Model
{
    protected $table = 'configuracoes_pagamento';

    protected $fillable = ['mercadopago_access_token', 'mercadopago_webhook_secret'];

    public static function atual(): self
    {
        return static::first() ?? static::create();
    }

    /**
     * Token da própria loja (criptografado no banco) se existir; senão
     * cai pro .env — útil em ambiente de desenvolvimento/teste, igual o
     * padrão já usado pra Melhor Envio em EntregaService.
     */
    public function accessToken(): ?string
    {
        return $this->descriptografar($this->mercadopago_access_token) ?? config('services.mercadopago.access_token');
    }

    public function webhookSecret(): ?string
    {
        return $this->descriptografar($this->mercadopago_webhook_secret) ?? config('services.mercadopago.webhook_secret');
    }

    private function descriptografar(?string $valor): ?string
    {
        if (!$valor) {
            return null;
        }

        try {
            return Crypt::decryptString($valor);
        } catch (\Exception $e) {
            Log::error('Não foi possível descriptografar uma credencial do Mercado Pago.');
            return null;
        }
    }
}
