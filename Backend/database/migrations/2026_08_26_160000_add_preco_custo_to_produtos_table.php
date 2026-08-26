<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('produtos', 'preco_custo')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->decimal('preco_custo', 10, 2)->default(0)->after('preco');
            });
        }
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('preco_custo');
        });
    }
};