<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrizeRequest;
use App\Http\Requests\UpdatePrizeRequest;
use App\Models\Prize;
use App\Models\RafflePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PrizeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $selectedPeriod = $request->input('raffle_period_id', '');
            $selectedPeriod = $selectedPeriod === null ? '' : $selectedPeriod; // guard ConvertEmptyStringsToNull
            $showAllPeriods = $selectedPeriod === '';

            $query = Prize::with('rafflePeriod')->withCount('winners');

            if (! $showAllPeriods) {
                $query->where('raffle_period_id', $selectedPeriod);
            }

            if ($request->filled('search')) {
                $query->where('name', 'like', '%'.$request->input('search').'%');
            }

            $query->orderBy($showAllPeriods ? 'raffle_period_id' : 'sequence');

            return DataTables::eloquent($query)
                ->addColumn('period_name', fn ($prize) => $prize->rafflePeriod?->name ?? '-')
                ->addColumn('urutan', fn ($prize) => [
                    'sequence' => $prize->sequence,
                    'move_up_url' => route('admin.prizes.move', $prize),
                    'move_down_url' => route('admin.prizes.move', $prize),
                    'is_used_in_drawing' => $prize->isUsedInDrawing(),
                ])
                ->addColumn('actions', fn ($prize) => [
                    'show_url' => route('admin.prizes.show', $prize),
                    'edit_url' => route('admin.prizes.edit', $prize),
                    'delete_url' => route('admin.prizes.destroy', $prize),
                ])
                ->make(true);
        }

        // Default periode: yang aktif & terbaru, dipakai untuk pre-select dropdown saat page-load pertama
        $activePeriod = RafflePeriod::where('status', 'active')
            ->orderByDesc('start_at')
            ->orderByDesc('id')
            ->first();

        return view('admin.prizes.index', [
            'periods' => RafflePeriod::orderBy('name')->get(),
            'defaultPeriodId' => $activePeriod?->id,
        ]);
    }

    public function create()
    {
        $periods = RafflePeriod::where('status', '!=', 'closed')->orderBy('name')->get();

        return view('admin.prizes.create', [
            'periods' => $periods,
        ]);
    }

    public function store(StorePrizeRequest $request)
    {
        $validated = $request->validated();

        $prize = Prize::create($validated);

        return redirect()->route('admin.prizes.index')
            ->with('success', "Hadiah '{$prize->name}' berhasil ditambahkan.");
    }

    public function show(Prize $prize)
    {
        $prize->load(['rafflePeriod', 'winners.customer', 'winners.coupon', 'drawings.executedBy']);

        return view('admin.prizes.show', [
            'prize' => $prize,
        ]);
    }

    public function edit(Prize $prize)
    {
        $periods = RafflePeriod::orderBy('name')->get();

        return view('admin.prizes.edit', [
            'prize' => $prize,
            'periods' => $periods,
            'isUsedInDrawing' => $prize->isUsedInDrawing(),
        ]);
    }

    public function update(UpdatePrizeRequest $request, Prize $prize)
    {
        $validated = $request->validated();

        $prize->update($validated);

        return redirect()->route('admin.prizes.index')
            ->with('success', "Hadiah '{$prize->name}' berhasil diperbarui.");
    }

    public function destroy(Prize $prize)
    {
        if ($prize->isUsedInDrawing()) {
            $message = "Hadiah '{$prize->name}' tidak dapat dihapus karena sudah digunakan dalam proses pengundian.";

            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $name = $prize->name;
        $prize->delete();

        $message = "Hadiah '{$name}' berhasil dihapus.";

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->route('admin.prizes.index')->with('success', $message);
    }

    public function move(Request $request, Prize $prize)
    {
        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        if ($prize->isUsedInDrawing()) {
            $message = "Urutan hadiah '{$prize->name}' tidak dapat diubah karena sudah digunakan dalam pengundian.";

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $neighborQuery = Prize::where('raffle_period_id', $prize->raffle_period_id);

        $neighbor = $direction === 'up'
            ? $neighborQuery->where('sequence', '<', $prize->sequence)->orderByDesc('sequence')->first()
            : $neighborQuery->where('sequence', '>', $prize->sequence)->orderBy('sequence')->first();

        if (! $neighbor) {
            $message = 'Hadiah sudah berada di ujung urutan.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if ($neighbor->isUsedInDrawing()) {
            $message = 'Tidak dapat menukar urutan dengan hadiah yang sudah digunakan dalam pengundian.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        DB::transaction(function () use ($prize, $neighbor) {
            $currentSequence = $prize->sequence;
            $neighborSequence = $neighbor->sequence;
            $temporarySequence = (int) Prize::where('raffle_period_id', $prize->raffle_period_id)->max('sequence') + 1000;

            $prize->update(['sequence' => $temporarySequence]);
            $neighbor->update(['sequence' => $currentSequence]);
            $prize->update(['sequence' => $neighborSequence]);
        });

        $message = "Urutan hadiah '{$prize->name}' berhasil diperbarui.";

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
