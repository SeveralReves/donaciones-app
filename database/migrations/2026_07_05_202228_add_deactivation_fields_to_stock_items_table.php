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
            $table->foreignId('deactivated_by')->nullable()->after('active')->constrained('users')->nullOnDelete();
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
            // No se borra al reactivar a propósito: queda como registro de
            // la última vez que se desactivó, y se sobrescribe solo la
            // próxima vez que alguien lo desactive de nuevo.
            $table->string('deactivation_reason')->nullable()->after('deactivated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deactivated_by');
            $table->dropColumn(['deactivated_at', 'deactivation_reason']);
        });
    }
};
