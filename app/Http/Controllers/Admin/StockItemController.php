<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Models\DonationType;
use App\Models\StockItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StockItemController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['donation_type']);

        $stockItems = StockItem::query()
            ->when($filters['donation_type'] ?? null, fn ($query, $type) => $query->where('donation_type', $type))
            ->with('deactivatedBy:id,name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Stock/Index', [
            'stockItems' => $stockItems,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Stock/Create', [
            'donationTypes' => DonationType::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(StoreDonationRequest::ITEM_UNITS)],
            'donation_type_id' => ['required', 'string', Rule::exists('donation_types', 'id')->where('active', true)],
            'quantity_available' => ['nullable', 'numeric', 'min:0'],
            'minimum_threshold' => ['nullable', 'numeric', 'min:0'],
        ]);

        // donation_type (texto) todavía es NOT NULL y otras pantallas sin
        // migrar (filtros del listado, /necesidades) siguen leyéndola; se
        // deriva del tipo elegido en vez de pedírsela al cliente. Se elimina
        // en un paso posterior.
        $validated['donation_type'] = DonationType::findOrFail($validated['donation_type_id'])->slug;

        StockItem::create([
            ...$validated,
            'quantity_available' => $validated['quantity_available'] ?? 0,
        ]);

        return redirect()->route('admin.stock-items.index');
    }

    /**
     * Edición rápida del umbral mínimo desde el listado: no pasa por el
     * formulario de creación ni afecta el resto de campos del insumo.
     */
    public function updateThreshold(Request $request, StockItem $stockItem): RedirectResponse
    {
        $validated = $request->validate([
            // null explícito = "no monitorear este insumo", no un umbral de 0.
            'minimum_threshold' => ['nullable', 'numeric', 'min:0'],
        ]);

        $stockItem->update($validated);

        return redirect()->route('admin.stock-items.index');
    }

    /**
     * No es un borrado real: solo apaga 'active' y deja registro de quién,
     * cuándo y por qué. El catálogo de donaciones y /necesidades ya filtran
     * por active=true, así que un insumo desactivado deja de ofrecerse ahí
     * sin necesidad de tocar esas pantallas.
     */
    public function deactivate(Request $request, StockItem $stockItem): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $stockItem->update([
            'active' => false,
            'deactivated_by' => $request->user()->id,
            'deactivated_at' => now(),
            'deactivation_reason' => $validated['reason'],
        ]);

        return redirect()->route('admin.stock-items.index');
    }

    /**
     * deactivated_by/deactivated_at/deactivation_reason no se limpian a
     * propósito: quedan como el registro de la última desactivación hasta
     * que se sobrescriban la próxima vez que alguien desactive este insumo.
     */
    public function reactivate(StockItem $stockItem): RedirectResponse
    {
        $stockItem->update(['active' => true]);

        return redirect()->route('admin.stock-items.index');
    }

    /**
     * Historial de ajustes de un insumo específico, más reciente primero.
     */
    public function adjustments(StockItem $stockItem): Response
    {
        return Inertia::render('Admin/Stock/Adjustments', [
            'stockItem' => $stockItem,
            'adjustments' => $stockItem->adjustments()
                ->with('changedBy:id,name')
                ->orderByDesc('changed_at')
                ->get(),
        ]);
    }

    /**
     * Único punto de entrada para cambiar quantity_available a mano: deja
     * registro en stock_adjustments y nunca permite que quede negativa.
     */
    public function adjustStock(Request $request, StockItem $stockItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity_change' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($stockItem, $validated, $request): void {
            // Bloquea la fila para evitar que dos ajustes concurrentes lean
            // la misma cantidad disponible y ambos pasen la validación de
            // "no negativo" antes de que el otro se aplique.
            $locked = StockItem::whereKey($stockItem->id)->lockForUpdate()->firstOrFail();

            $newQuantity = $locked->quantity_available + $validated['quantity_change'];

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity_change' => "No hay suficiente stock disponible: quedarían {$newQuantity} {$locked->unit} (actual: {$locked->quantity_available}).",
                ]);
            }

            $locked->adjustments()->create([
                'quantity_change' => $validated['quantity_change'],
                'reason' => $validated['reason'],
                'changed_by' => $request->user()->id,
                'changed_at' => now(),
            ]);

            $locked->increment('quantity_available', $validated['quantity_change']);
        });

        return redirect()->route('admin.stock-items.index');
    }
}
