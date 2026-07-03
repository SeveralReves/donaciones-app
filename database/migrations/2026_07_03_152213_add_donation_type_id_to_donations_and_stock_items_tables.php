<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignUuid('donation_type_id')
                ->nullable()
                ->after('donation_type')
                ->constrained('donation_types')
                ->nullOnDelete();
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->foreignUuid('donation_type_id')
                ->nullable()
                ->after('donation_type')
                ->constrained('donation_types')
                ->nullOnDelete();
        });

        // donation_type (texto) se deja intacta a propósito como respaldo
        // temporal — se elimina en una migración aparte una vez confirmado
        // que esta migración de datos quedó 100% correcta.
        $this->backfillDonationTypeId('donations');
        $this->backfillDonationTypeId('stock_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('donation_type_id');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('donation_type_id');
        });
    }

    /**
     * Llena donation_type_id en $table buscando, para cada valor de texto
     * de donation_type, el registro de donation_types cuyo slug coincida
     * exactamente. Un UPDATE por slug (no por fila) para no recorrer la
     * tabla en PHP. Cualquier valor de donation_type sin slug conocido
     * queda reportado explícitamente, no se ignora en silencio.
     */
    private function backfillDonationTypeId(string $table): void
    {
        $donationTypeIdsBySlug = DB::table('donation_types')->pluck('id', 'slug');

        $updated = 0;

        foreach ($donationTypeIdsBySlug as $slug => $donationTypeId) {
            $updated += DB::table($table)
                ->where('donation_type', $slug)
                ->update(['donation_type_id' => $donationTypeId]);
        }

        echo "\n    {$table}: {$updated} registro(s) actualizados con donation_type_id.\n";

        $orphans = DB::table($table)
            ->whereNull('donation_type_id')
            ->select('donation_type', DB::raw('count(*) as total'))
            ->groupBy('donation_type')
            ->get();

        if ($orphans->isEmpty()) {
            echo "    {$table}: sin registros huérfanos (todos los donation_type tienen slug conocido).\n";

            return;
        }

        echo "    {$table}: ¡ATENCIÓN! valores de donation_type sin coincidencia en donation_types.slug:\n";

        foreach ($orphans as $orphan) {
            echo "      - \"{$orphan->donation_type}\": {$orphan->total} registro(s)\n";
        }
    }
};
