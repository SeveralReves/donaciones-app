<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Models\MedicalReceiver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'donation_type', 'location']);

        $donations = Donation::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['donation_type'] ?? null, fn ($query, $type) => $query->where('donation_type', $type))
            ->when($filters['location'] ?? null, fn ($query, $location) => $query->where('location', 'like', "%{$location}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Donations/Index', [
            'donations' => $donations,
            'filters' => $filters,
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
}
