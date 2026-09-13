<?php

namespace App\Http\Controllers;

use App\Models\RafflePeriod;
use App\Models\Winner;
use Illuminate\Http\Request;

class WinnerController extends Controller
{
    public function index(Request $request)
    {
        $periods = RafflePeriod::query()
            ->whereHas('winners', fn ($query) => $query->where('is_published', true))
            ->orderByDesc('start_at')
            ->get(['id', 'name']);

        $winners = Winner::query()
            ->where('is_published', true)
            ->with(['customer', 'prize', 'rafflePeriod'])
            ->when($request->integer('period_id'), fn ($query, $periodId) => $query->where('raffle_period_id', $periodId))
            ->latest('won_at')
            ->paginate(24)
            ->withQueryString();

        return view('winners.index', compact('periods', 'winners'));
    }
}
