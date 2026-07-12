<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('app:import-children-json {path} {--pretend : Simula la importación completa y hace rollback al final, sin dejar cambios en la base de datos}')]
#[Description('Importa niños y sus necesidades desde un archivo JSON (ver formato en el propio comando)')]
class ImportChildrenJson extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("No se encontró el archivo: {$path}");

            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($items)) {
            $this->error('El archivo no contiene JSON válido (se espera un array de objetos).');

            return self::FAILURE;
        }

        // "Usuario sistema" para created_by/fulfilled_by: children.created_by
        // es NOT NULL, así que no existe la opción de dejarlo en null — se
        // usa el primer super_admin como lo pidió el prompt.
        $systemUser = User::where('rol', 'super_admin')->oldest('id')->first();

        if (! $systemUser) {
            $this->error('No existe ningún super_admin en la base de datos — se necesita uno para usar como "usuario sistema" (created_by/fulfilled_by). Creá uno antes de importar.');

            return self::FAILURE;
        }

        $pretend = (bool) $this->option('pretend');

        $created = 0;
        $skipped = 0;
        $errors = 0;
        $needsReviewNames = [];

        DB::beginTransaction();

        foreach ($items as $index => $entry) {
            try {
                $result = DB::transaction(
                    fn () => $this->importOne($entry, $systemUser)
                );
            } catch (Throwable $e) {
                $errors++;
                $name = is_array($entry) ? ($entry['name'] ?? '(sin name)') : '(entrada inválida)';
                $this->error("Fila #{$index} (\"{$name}\") no se pudo importar: {$e->getMessage()}");

                continue;
            }

            if ($result['skipped']) {
                $skipped++;
            } else {
                $created++;

                if ($result['needs_review']) {
                    $needsReviewNames[] = $result['name'];
                }
            }
        }

        if ($pretend) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $this->newLine();
        $this->info($pretend ? 'SIMULACIÓN (--pretend): no se escribió nada en la base de datos.' : 'Importación completada.');
        $this->info("Niños creados: {$created}");
        $this->info("Omitidos (ya existían): {$skipped}");

        if ($errors > 0) {
            $this->warn("Filas con error: {$errors}");
        }

        if (! empty($needsReviewNames)) {
            $this->newLine();
            $this->warn('Quedaron con needs_review = true (requieren seguimiento manual):');

            foreach ($needsReviewNames as $name) {
                $this->line(" - {$name}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Procesa una entrada del JSON. Ya corre dentro de una transacción
     * propia (savepoint de la transacción externa que envuelve todo el
     * comando, la que --pretend termina revirtiendo) — si algo falla acá,
     * solo se deshace esta fila, no el resto de la importación.
     *
     * @return array{skipped: bool, name: string, needs_review: bool}
     */
    private function importOne(mixed $entry, User $systemUser): array
    {
        if (! is_array($entry) || blank($entry['name'] ?? null)) {
            throw new \InvalidArgumentException('Falta el campo "name".');
        }

        $name = $entry['name'];
        $phone = $entry['phone'] ?? null;

        // Mismo name Y mismo teléfono: evita duplicados si el comando se
        // corre dos veces por error con el mismo archivo.
        $alreadyExists = Child::query()
            ->where('name', $name)
            ->where('guardian_phone', $phone)
            ->exists();

        if ($alreadyExists) {
            return ['skipped' => true, 'name' => $name, 'needs_review' => false];
        }

        $needsReview = (bool) ($entry['needs_review'] ?? false);

        $child = Child::create([
            'name' => $name,
            'guardian_name' => $entry['guardian_name'] ?? null,
            'guardian_phone' => $phone,
            'address' => $entry['address'] ?? null,
            // Texto libre tal como viene reportada — no se intenta mapear
            // contra el catálogo de conditions, eso lo hace el admin a mano
            // después desde la interfaz.
            'condition_notes' => $entry['condition_notes'] ?? null,
            'needs_review' => $needsReview,
            'created_by' => $systemUser->id,
        ]);

        foreach ($entry['needs'] ?? [] as $needData) {
            $isMedication = (bool) ($needData['is_medication'] ?? false);
            $status = $needData['status'] ?? 'pendiente';
            $isCovered = $status === 'cubierta';

            $need = $child->needs()->create([
                'description' => $needData['description'] ?? '',
                'status' => $status,
                'is_recurring' => $isMedication,
                'recurrence_interval_days' => $isMedication ? 30 : null,
                // Si no se registra acá, una necesidad recurrente importada
                // ya "cubierta" nunca sería candidata a reabrirse
                // (app:reopen-recurring-needs exige last_covered_at no nulo).
                'last_covered_at' => $isCovered ? now() : null,
                'created_by' => $systemUser->id,
            ]);

            if ($isCovered) {
                $need->fulfillments()->create([
                    'donation_id' => null,
                    'fulfilled_by' => $systemUser->id,
                    'fulfilled_at' => now(),
                    'notes' => 'Importado como ya cubierto',
                ]);
            }
        }

        return ['skipped' => false, 'name' => $name, 'needs_review' => $needsReview];
    }
}
