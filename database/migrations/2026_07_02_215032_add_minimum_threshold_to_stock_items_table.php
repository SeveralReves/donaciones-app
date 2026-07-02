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
            // Null a propósito: significa "no monitorear este insumo como
            // necesidad", incluso si su stock llega a cero. No es lo mismo
            // que un umbral de 0.
            $table->decimal('minimum_threshold', 10, 2)->nullable()->after('quantity_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('minimum_threshold');
        });
    }
};
