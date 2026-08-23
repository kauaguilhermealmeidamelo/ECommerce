<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cartoes_salvos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            // Nunca armazenar número, CVV ou validade completa.
            // Apenas os identificadores que o Mercado Pago devolve após tokenizar o cartão.
            $table->string('mp_customer_id');
            $table->string('mp_card_id');
            $table->string('bandeira')->nullable(); // visa, mastercard...
            $table->string('ultimos_digitos', 4)->nullable();
            $table->boolean('padrao')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoes_salvos');
    }
};
