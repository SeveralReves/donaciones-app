<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $statusCounts = Donation::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $typeCounts = Donation::query()
            ->select('donation_type', DB::raw('count(*) as total'))
            ->groupBy('donation_type')
            ->pluck('total', 'donation_type');

        $recentDonations = Donation::query()
            ->with('creator:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'donation_type', 'location', 'status', 'created_at', 'created_by']);

        return Inertia::render('Dashboard', [
            'totalDonations' => Donation::count(),
            'totalItems' => DonationItem::count(),
            'donationsToday' => Donation::whereDate('created_at', today())->count(),
            'donationsThisWeek' => Donation::where('created_at', '>=', now()->startOfWeek())->count(),
            'statusCounts' => $statusCounts,
            'typeCounts' => $typeCounts,
            'recentDonations' => $recentDonations,
        ]);
    }
}
