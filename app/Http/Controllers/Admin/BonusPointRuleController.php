<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBonusPointRuleRequest;
use App\Http\Requests\UpdateBonusPointRuleRequest;
use App\Models\BonusPointRule;
use App\Models\PaymentType;
use App\Models\RafflePeriod;
use Illuminate\Http\Request;

class BonusPointRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = BonusPointRule::with(['rafflePeriod', 'paymentType'])->latest();

        if ($request->filled('raffle_period_id')) {
            $query->where('raffle_period_id', $request->integer('raffle_period_id'));
        }

        return view('admin.bonus-point-rules.index', [
            'rules' => $query->paginate(15)->withQueryString(),
            'periods' => RafflePeriod::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.bonus-point-rules.create', [
            'periods' => RafflePeriod::orderBy('name')->get(),
            'paymentTypes' => PaymentType::orderBy('name')->get(),
        ]);
    }

    public function store(StoreBonusPointRuleRequest $request)
    {
        BonusPointRule::create($request->validated());

        return redirect()->route('admin.bonus-point-rules.index')->with('success', 'Bonus poin berhasil ditambahkan.');
    }

    public function edit(BonusPointRule $bonusPointRule)
    {
        return view('admin.bonus-point-rules.edit', [
            'bonusPointRule' => $bonusPointRule,
            'periods' => RafflePeriod::orderBy('name')->get(),
            'paymentTypes' => PaymentType::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBonusPointRuleRequest $request, BonusPointRule $bonusPointRule)
    {
        $bonusPointRule->update($request->validated());

        return redirect()->route('admin.bonus-point-rules.index')->with('success', 'Bonus poin berhasil diperbarui.');
    }

    public function destroy(BonusPointRule $bonusPointRule)
    {
        $bonusPointRule->delete();

        return redirect()->route('admin.bonus-point-rules.index')->with('success', 'Bonus poin berhasil dihapus.');
    }

    public function toggleStatus(BonusPointRule $bonusPointRule)
    {
        $bonusPointRule->update(['is_active' => ! $bonusPointRule->is_active]);

        return back()->with('success', 'Status bonus poin berhasil diperbarui.');
    }
}
