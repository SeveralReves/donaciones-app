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
        Schema::table('donation_items', function (Blueprint $table) {
            // Solo tiene sentido cuando unit = 'cajas'; queda null para
            // cualquier otra unidad (DonationController se encarga de
            // garantizar esa consistencia al guardar).
            $table->integer('units_per_box')->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_items', function (Blueprint $table) {
            $table->dropColumn('units_per_box');
        });
    }
};
