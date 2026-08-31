<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton (mesmo padrão de configuracoes_entrega): uma linha só,
     * lida sempre via ConfiguracaoPagamento::atual(). Os dois campos
     * sensíveis nunca são salvos em texto puro — sempre via
     * Crypt::encryptString() (ver ConfiguracaoPagamentoController).
     */
    public function up(): void
    {
        Schema::create('configuracoes_pagamento', function (Blueprint $table) {
            $table->id();
            $table->text('mercadopago_access_token')->nullable();
            $table->text('mercadopago_webhook_secret')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_pagamento');
    }
};
