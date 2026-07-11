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
        Schema::create('child_need_fulfillments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('child_need_id')->constrained('child_needs')->cascadeOnDelete();
            // Nullable porque una necesidad se puede cubrir sin pasar por el
            // sistema de donaciones (ej. ayuda psicológica gestionada aparte).
            $table->foreignUuid('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->foreignId('fulfilled_by')->constrained('users');
            $table->timestamp('fulfilled_at');
            $table->string('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_need_fulfillments');
    }
};
