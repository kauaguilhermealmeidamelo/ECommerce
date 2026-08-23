<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('itens_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('variacao_id')->nullable()->constrained('produto_variacoes')->nullOnDelete();
            $table->unsignedInteger('quantidade');
            // Preço travado no momento da compra — nunca referenciar o preço atual do produto.
            $table->decimal('preco_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_pedido');
    }
};
