<?php

namespace App\Http\Controllers\Children;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildNeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Protegido por la Gate 'access-children-module' a nivel de grupo de rutas
 * (routes/web.php), igual que ChildController.
 */
class ChildNeedController extends Controller
{
    public function store(Request $request, Child $child): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurrence_interval_days' => [
                Rule::requiredIf($request->boolean('is_recurring')),
                'nullable', 'integer', 'min:1',
            ],
        ]);

        // Solo tiene sentido si la necesidad es recurrente — se ignora en
        // vez de rechazar la petición si viene igual para una que no lo es.
        if (empty($validated['is_recurring'])) {
            $validated['recurrence_interval_days'] = null;
        }

        $child->needs()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('children.show', $child);
    }

    /**
     * Marca la necesidad como cubierta y deja registro en
     * child_need_fulfillments — donation_id queda null acá porque cubrir una
     * necesidad no siempre pasa por el sistema de donaciones (p. ej. ayuda
     * psicológica gestionada aparte). Se conecta con una donación real en un
     * paso posterior.
     */
    public function markCovered(Request $request, ChildNeed $childNeed): RedirectResponse
    {
        DB::transaction(function () use ($request, $childNeed): void {
            $now = now();

            $childNeed->update([
                'status' => 'cubierta',
                'last_covered_at' => $now,
            ]);

            $childNeed->fulfillments()->create([
                'donation_id' => null,
                'fulfilled_by' => $request->user()->id,
                'fulfilled_at' => $now,
            ]);
        });

        return redirect()->route('children.show', $childNeed->child_id);
    }

    /**
     * Lleva al formulario de nueva donación con los datos del niño
     * pre-llenados y una referencia a esta necesidad como query param (en
     * vez de sesión, para que sobreviva a pestañas/back-forward sin quedar
     * "pegada" a la próxima donación que se cree). DonationController@store
     * la usa para marcar esta necesidad como cubierta si la donación se
     * guarda con éxito.
     */
    public function initiateDonationForNeed(ChildNeed $childNeed): RedirectResponse
    {
        $childNeed->loadMissing('child');

        return redirect()->route('donations.create', [
            'patient_name' => $childNeed->child->name,
            'location' => $childNeed->child->address,
            'contact_number' => $childNeed->child->guardian_phone,
            'item_description' => $childNeed->description,
            'child_need_id' => $childNeed->id,
        ]);
    }
}
