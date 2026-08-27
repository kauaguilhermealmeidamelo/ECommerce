<?php
// Backend/database/migrations/2026_08_27_000001_add_categoria_pai_id_to_categorias_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('categoria_pai_id')->nullable()->after('id')
                ->constrained('categorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_pai_id');
        });
    }
};