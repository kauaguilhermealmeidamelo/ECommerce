<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->string('pagina')->nullable();
            $table->string('sessao_id', 64)->index(); // reaproveita o X-Session-Id do carrinho
            $table->string('ip_hash', 64); // hash do IP — nunca guardamos IP puro (LGPD)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
