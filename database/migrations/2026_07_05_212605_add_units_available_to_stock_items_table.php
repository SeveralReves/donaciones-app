<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // null a propósito mientras nunca se haya especificado
            // units_per_box en ningún movimiento de este insumo — distingue
            // "no lo sabemos" de "es cero".
            $table->integer('units_available')->nullable()->default(null)->after('quantity_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('units_available');
        });
    }
};
