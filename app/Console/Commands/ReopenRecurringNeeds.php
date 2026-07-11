<?php

namespace App\Console\Commands;

use App\Models\ChildNeed;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:reopen-recurring-needs')]
#[Description('Reabre las necesidades recurrentes del módulo de niños cuyo ciclo de cobertura ya venció')]
class ReopenRecurringNeeds extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // El vencimiento (last_covered_at + recurrence_interval_days) no es
        // expresable como where() simple ni portable entre motores de BD, y
        // el volumen de necesidades recurrentes/cubiertas es chico — se
        // acota la consulta con lo que sí es indexable/simple y el cálculo
        // de fecha se hace en PHP con Carbon.
        $candidates = ChildNeed::query()
            ->with('child:id,name')
            ->where('status', 'cubierta')
            ->where('is_recurring', true)
            ->whereNotNull('last_covered_at')
            ->whereNotNull('recurrence_interval_days')
            ->get();

        $due = $candidates->filter(
            fn (ChildNeed $need) => $need->last_covered_at
                ->copy()
                ->addDays($need->recurrence_interval_days)
                ->lte(now()),
        );

        if ($due->isEmpty()) {
            $this->info('No hay necesidades recurrentes para reabrir.');

            return self::SUCCESS;
        }

        foreach ($due as $need) {
            // El historial de child_need_fulfillments no se toca: solo
            // cambia el status para que la necesidad vuelva a aparecer como
            // pendiente, sin borrar ni modificar ningún fulfillment previo.
            $need->update(['status' => 'pendiente']);

            $this->line("Reabierta: \"{$need->description}\" de {$need->child->name}");
        }

        $this->info("Se reabrieron {$due->count()} necesidad(es) recurrente(s).");

        return self::SUCCESS;
    }
}
