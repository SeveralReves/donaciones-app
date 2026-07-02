<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\UpdateDonationStatusRequest;
use App\Models\Donation;
use App\Models\MedicalReceiver;
use App\Services\DonationStatusFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return Inertia::render('Donations/Create');
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
