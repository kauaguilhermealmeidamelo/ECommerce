<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carrinho_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrinho_id')->constrained('carrinhos')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->foreignId('variacao_id')->nullable()->constrained('produto_variacoes')->nullOnDelete();
            $table->unsignedInteger('quantidade')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrinho_itens');
    }
};
