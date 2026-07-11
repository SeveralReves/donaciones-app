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
        Schema::create('child_needs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('description');
            $table->string('status')->default('pendiente');
            $table->boolean('is_recurring')->default(false);
            // Obligatorio solo si is_recurring es true (p. ej. 30 para
            // mensual) — esa condición se valida a nivel de negocio, no acá.
            $table->integer('recurrence_interval_days')->nullable();
            $table->timestamp('last_covered_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_needs');
    }
};
