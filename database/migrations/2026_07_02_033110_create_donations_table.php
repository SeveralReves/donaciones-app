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
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('patient_name')->nullable();
            $table->string('donation_type');
            $table->string('location');
            $table->string('receiving_doctor_name')->nullable();
            $table->string('receiving_doctor_code')->nullable();
            $table->string('receiving_service')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('cedula')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('delivery_name')->nullable();
            $table->string('delivery_contact')->nullable();
            $table->string('status')->default('creada');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
