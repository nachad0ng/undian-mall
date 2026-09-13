<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PointRedemption;
use App\Models\Tenant;
use App\Models\Winner;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames();
        $recentWinners = Winner::query()
            ->where('is_published', true)
            ->with(['customer', 'prize', 'rafflePeriod'])
            ->latest('won_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'user' => $user,
            'roles' => $roles,
            'customerCount' => Customer::count(),
            'tenantCount' => Tenant::count(),
            'redemptionCount' => PointRedemption::successful()->count(),
            'winnerCount' => Winner::where('is_published', true)->count(),
            'recentWinners' => $recentWinners,
        ]);
    }
}
