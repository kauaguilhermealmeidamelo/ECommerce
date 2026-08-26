<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_entrega', function (Blueprint $table) {
            $table->id();
            $table->boolean('retirada_ativa')->default(true);
            $table->boolean('entrega_local_ativa')->default(false);
            $table->boolean('transportadora_ativa')->default(false);
            $table->text('token_melhor_envio')->nullable(); // salvo via Crypt::encryptString()
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_entrega');
    }
};
