<?php

namespace App\Http\Controllers;

use App\Models\AdditionalNeed;
use App\Models\StockItem;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Página pública (sin autenticación): cualquiera con el link puede ver qué
 * necesita la fundación en este momento. Es de solo lectura a propósito, no
 * expone ninguna acción de escritura.
 */
class NeedsController extends Controller
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

        $additionalNeeds = AdditionalNeed::query()
            ->where('active', true)
            ->oldest()
            ->get(['id', 'description', 'quantity_needed']);

        return Inertia::render('Needs', [
            'stockNeeds' => $stockNeeds,
            'additionalNeeds' => $additionalNeeds,
        ]);
    }
}
