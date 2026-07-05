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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Solo aplica cuando el stock_item correspondiente tiene
            // unit = 'cajas'; StockItemController lo ignora (queda null)
            // para cualquier otra unidad.
            $table->integer('units_per_box')->nullable()->after('quantity_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('units_per_box');
        });
    }
};
