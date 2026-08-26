<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrinhos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sessao_id', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('itens_carrinho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrinho_id')->constrained('carrinhos')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('preco_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_carrinho');
        Schema::dropIfExists('carrinhos');
    }
};
