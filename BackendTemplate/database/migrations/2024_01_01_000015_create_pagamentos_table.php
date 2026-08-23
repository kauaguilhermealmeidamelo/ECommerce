<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('mp_payment_id')->unique();
            $table->string('status'); // approved, pending, rejected...
            $table->string('metodo_pagamento')->nullable(); // pix, credit_card, boleto...
            $table->decimal('valor', 10, 2);
            $table->json('payload_json')->nullable(); // corpo bruto recebido do webhook, para auditoria
            $table->timestamp('recebido_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
