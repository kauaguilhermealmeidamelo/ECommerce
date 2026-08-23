<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabela separada de "clientes" de propósito: dono da loja e você (suporte)
        // nunca devem logar pela mesma porta usada pelos compradores.
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha_hash');
            $table->enum('papel', ['owner', 'suporte'])->default('owner');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
