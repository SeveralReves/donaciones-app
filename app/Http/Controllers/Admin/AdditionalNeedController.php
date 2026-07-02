<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdditionalNeed;
use App\Models\StockItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdditionalNeedController extends Controller
{
    public function index(): Response
    {
        $stockNeeds = StockItem::query()
            ->belowThreshold()
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'donation_type', 'quantity_available', 'minimum_threshold'])
            ->map(fn (StockItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'donation_type' => $item->donation_type,
                'quantity_needed' => number_format($item->minimum_threshold - $item->quantity_available, 2, '.', ''),
            ])
            ->values();

        return Inertia::render('Admin/Needs/Index', [
            'stockNeeds' => $stockNeeds,
            // Incluye inactivas para poder reactivarlas; la página pública
            // en cambio solo muestra las activas.
            'additionalNeeds' => AdditionalNeed::query()->latest()->get(),
            'publicNeedsUrl' => route('needs.index'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity_needed' => ['nullable', 'string', 'max:255'],
        ]);

        AdditionalNeed::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.needs.index');
    }

    public function toggleActive(AdditionalNeed $additionalNeed): RedirectResponse
    {
        $additionalNeed->update(['active' => ! $additionalNeed->active]);

        return redirect()->route('admin.needs.index');
    }
}
