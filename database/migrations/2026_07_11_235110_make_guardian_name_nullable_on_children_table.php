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
        // guardian_phone sigue siendo el dato crítico (obligatorio): a veces
        // no se sabe el nombre exacto del tutor, pero sin teléfono no hay
        // forma de contactarlo.
        Schema::table('children', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('guardian_name')->nullable(false)->change();
        });
    }
};
