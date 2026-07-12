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
        Schema::table('children', function (Blueprint $table) {
            // Texto libre tal como fue reportada la condición, antes de que
            // el admin la etiquete contra el catálogo de conditions.
            $table->text('condition_notes')->nullable()->after('address');
            // Marca registros importados con datos incompletos (ej. sin
            // teléfono) que requieren que el admin los complete a mano.
            $table->boolean('needs_review')->default(false)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['condition_notes', 'needs_review']);
        });
    }
};
