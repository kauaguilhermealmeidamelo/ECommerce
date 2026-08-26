<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pendente');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('itens_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('preco_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_pedido');
        Schema::dropIfExists('pedidos');
    }
};
