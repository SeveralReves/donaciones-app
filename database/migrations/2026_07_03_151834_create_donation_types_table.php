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
        Schema::create('donation_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            // Debe coincidir exactamente con los valores de texto que ya
            // existen hoy en donations.donation_type y
            // stock_items.donation_type, para poder migrarlos en el
            // siguiente paso sin necesidad de mapear valores.
            $table->string('slug')->unique();
            $table->boolean('requires_doctor_data')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_types');
    }
};
