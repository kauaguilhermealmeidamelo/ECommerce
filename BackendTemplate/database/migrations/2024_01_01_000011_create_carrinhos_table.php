<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carrinhos', function (Blueprint $table) {
            $table->id();
            // nullable: carrinho de visitante ainda não logado
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->cascadeOnDelete();
            $table->string('session_id')->nullable()->index(); // usado quando cliente_id é nulo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrinhos');
    }
};
