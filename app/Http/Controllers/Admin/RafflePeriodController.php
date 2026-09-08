<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRafflePeriodRequest;
use App\Http\Requests\UpdateRafflePeriodRequest;
use App\Models\RafflePeriod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RafflePeriodController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $query = RafflePeriod::withCount(['prizes', 'purchases', 'coupons'])
                ->orderByDesc('start_at');

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('custom_search')) {
                $search = $request->input('custom_search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            return DataTables::eloquent($query)
                ->addColumn('actions', fn ($period) => [
                    'show_url' => route('admin.raffle-periods.show', $period),
                    'edit_url' => route('admin.raffle-periods.edit', $period),
                    'toggle_url' => route('admin.raffle-periods.toggle-status', $period),
                    'delete_url' => route('admin.raffle-periods.destroy', $period),
                    'toggle_title' => $period->status === 'active' ? 'Nonaktifkan' : 'Aktifkan',
                ])
                ->make(true);
        }

        return view('admin.raffle-periods.index');
    }

    public function create()
    {
        return view('admin.raffle-periods.create');
    }

    public function store(StoreRafflePeriodRequest $request)
    {
        $validated = $request->validated();
        $validated['drawing_status'] = 'pending';

        $period = RafflePeriod::create($validated);

        return redirect()->route('admin.raffle-periods.index')
            ->with('success', "Periode undian '{$period->name}' berhasil dibuat.");
    }

    public function show(RafflePeriod $rafflePeriod)
    {
        $rafflePeriod->loadCount(['prizes', 'purchases', 'coupons', 'winners']);
        $prizes = $rafflePeriod->prizes()->orderBy('sequence', 'asc')->get();

        return view('admin.raffle-periods.show', [
            'period' => $rafflePeriod,
            'prizes' => $prizes,
        ]);
    }

    public function edit(RafflePeriod $rafflePeriod)
    {
        return view('admin.raffle-periods.edit', [
            'period' => $rafflePeriod,
        ]);
    }

    public function update(UpdateRafflePeriodRequest $request, RafflePeriod $rafflePeriod)
    {
        $validated = $request->validated();

        $rafflePeriod->update($validated);

        return redirect()->route('admin.raffle-periods.index')
            ->with('success', "Periode undian '{$rafflePeriod->name}' berhasil diperbarui.");
    }

    public function destroy(RafflePeriod $rafflePeriod)
    {
        if ($rafflePeriod->purchases()->exists() || $rafflePeriod->drawings()->exists()) {
            return back()->with('error', "Periode '{$rafflePeriod->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi atau pengundian.");
        }

        $periodName = $rafflePeriod->name;
        $rafflePeriod->delete();

        return redirect()->route('admin.raffle-periods.index')
            ->with('success', "Periode '{$periodName}' berhasil dihapus.");
    }

    public function toggleStatus(RafflePeriod $rafflePeriod)
    {
        if ($rafflePeriod->status === 'closed') {
            return back()->with('error', "Periode '{$rafflePeriod->name}' sudah ditutup dan tidak dapat diubah statusnya.");
        }

        if ($rafflePeriod->status === 'active') {
            $rafflePeriod->update(['status' => 'inactive']);

            return back()->with('success', "Status periode '{$rafflePeriod->name}' berhasil diubah menjadi inactive.");
        }

        if (! $rafflePeriod->canBeActivated()) {
            return back()->with('error', "Periode '{$rafflePeriod->name}' tidak dapat diaktifkan. Pastikan aturan kupon lengkap dan pengundian belum selesai.");
        }

        $rafflePeriod->update(['status' => 'active']);

        return back()->with('success', "Status periode '{$rafflePeriod->name}' berhasil diubah menjadi active.");
    }
}
