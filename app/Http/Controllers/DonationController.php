<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\UpdateDonationStatusRequest;
use App\Models\Donation;
use App\Models\MedicalReceiver;
use App\Models\StockItem;
use App\Services\DonationStatusFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'donation_type', 'location', 'from', 'to']);

        $withCommonFilters = fn () => Donation::query()
            ->when($filters['donation_type'] ?? null, fn ($query, $type) => $query->where('donation_type', $type))
            ->when($filters['location'] ?? null, fn ($query, $location) => $query->where('location', 'like', "%{$location}%"))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to));

        $donations = $withCommonFilters()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->with('creator:id,name')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusCounts = $withCommonFilters()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Donations/Index', [
            'donations' => $donations,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create-donations');

        return Inertia::render('Donations/Create', [
            'stockItems' => StockItem::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'donation_type', 'quantity_available']),
        ]);
    }

    public function store(StoreDonationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        DB::transaction(function () use ($validated, $items, $request): void {
            $donation = Donation::create([
                ...$validated,
                'status' => 'creada',
                'created_by' => $request->user()->id,
            ]);

            foreach ($items as $item) {
                if ($item['stock_item_id'] ?? null) {
                    // Bloquea la fila: dos donaciones creándose a la vez no
                    // deben poder leer la misma cantidad disponible y
                    // descontar ambas más de lo que realmente hay.
                    $stockItem = StockItem::whereKey($item['stock_item_id'])->lockForUpdate()->firstOrFail();

                    if ($item['quantity'] > $stockItem->quantity_available) {
                        throw ValidationException::withMessages([
                            'items' => "No hay suficiente stock de \"{$stockItem->name}\" para completar la donación.",
                        ]);
                    }

                    // El nombre/unidad se toman del catálogo, no de lo que
                    // haya mandado el cliente, para que el registro de la
                    // donación quede consistente con el insumo real.
                    $item['name'] = $stockItem->name;
                    $item['unit'] = $stockItem->unit;

                    $stockItem->decrement('quantity_available', $item['quantity']);
                }

                $donation->items()->create($item);
            }

            if (! empty($validated['receiving_doctor_code'])) {
                MedicalReceiver::updateOrCreate(
                    ['codigo_medico' => $validated['receiving_doctor_code']],
                    [
                        'name' => $validated['receiving_doctor_name'] ?? '',
                        'servicio' => $validated['receiving_service'] ?? null,
                    ],
                );
            }

            $donation->statusLogs()->create([
                'from_status' => null,
                'to_status' => 'creada',
                'changed_by' => $request->user()->id,
                'changed_at' => now(),
            ]);
        });

        return redirect()->route('donations.index');
    }

    public function show(Donation $donation): Response
    {
        $donation->load('items');

        $statusLogs = $donation->statusLogs()
            ->with('changedBy:id,name')
            ->orderBy('changed_at')
            ->get();

        $nextStatus = DonationStatusFlow::nextStatus($donation->status);

        return Inertia::render('Donations/Show', [
            'donation' => $donation,
            'statusLogs' => $statusLogs,
            'nextStatus' => $nextStatus,
            'missingFields' => $nextStatus
                ? DonationStatusFlow::missingFields($donation, $nextStatus)
                : [],
            'optionalFields' => $nextStatus
                ? DonationStatusFlow::optionalFields($donation, $nextStatus)
                : [],
            'fieldLabels' => DonationStatusFlow::fieldLabels(),
        ]);
    }

    public function updateStatus(UpdateDonationStatusRequest $request, Donation $donation): RedirectResponse
    {
        $validated = $request->validated();
        $targetStatus = $validated['status'];
        unset($validated['status']);

        DB::transaction(function () use ($donation, $targetStatus, $validated, $request): void {
            $fromStatus = $donation->status;

            foreach ($validated as $field => $value) {
                if (filled($value)) {
                    $donation->{$field} = $value;
                }
            }

            $donation->status = $targetStatus;
            $donation->save();

            if ($targetStatus === DonationStatusFlow::CANCELLED) {
                // Devuelve al catálogo solo lo que salió de él: los items
                // de texto libre (stock_item_id null) nunca descontaron
                // nada, así que no hay nada que revertir para ellos.
                foreach ($donation->items()->whereNotNull('stock_item_id')->get() as $item) {
                    StockItem::whereKey($item->stock_item_id)
                        ->lockForUpdate()
                        ->firstOrFail()
                        ->increment('quantity_available', $item->quantity);
                }
            }

            $donation->statusLogs()->create([
                'from_status' => $fromStatus,
                'to_status' => $targetStatus,
                'changed_by' => $request->user()->id,
                'changed_at' => now(),
            ]);
        });

        return redirect()->route('donations.show', $donation);
    }
}
