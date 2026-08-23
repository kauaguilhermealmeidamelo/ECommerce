<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->enum('tipo', ['percentual', 'fixo']);
            $table->decimal('valor', 10, 2);
            $table->date('validade')->nullable();
            $table->unsignedInteger('usos_max')->nullable();
            $table->unsignedInteger('usos_atual')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupons');
    }
};
