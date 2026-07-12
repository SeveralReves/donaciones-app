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
        // Los registros importados (app:import-children-json) pueden llegar
        // sin teléfono cuando needs_review queda en true — el admin lo
        // completa después a mano.
        Schema::table('children', function (Blueprint $table) {
            $table->string('guardian_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('guardian_phone')->nullable(false)->change();
        });
    }
};
