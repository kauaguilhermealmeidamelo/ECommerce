<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('endereco_id')->nullable()->constrained('enderecos')->nullOnDelete();
            $table->foreignId('cupom_id')->nullable()->constrained('cupons')->nullOnDelete();
            $table->enum('status', [
                'aguardando_pagamento', 'pago', 'enviado', 'entregue', 'cancelado',
            ])->default('aguardando_pagamento');
            $table->decimal('total', 10, 2);
            $table->decimal('frete', 10, 2)->default(0);
            $table->string('mercadopago_preference_id')->nullable();
            $table->string('mercadopago_payment_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
