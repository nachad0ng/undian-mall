<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointRedemptionRequest;
use App\Models\BonusPointRule;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\PointRedemption;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\Tenant;
use App\Services\PointCalculationService;
use App\Services\PointRedemptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PointExchangeController extends Controller
{
    public function __construct(
        private PointRedemptionService $redemptionService,
        private PointCalculationService $calculator,
    ) {}

    // ---------------------------------------------------------------------
    // API: hadiah aktif untuk periode
    // ---------------------------------------------------------------------
    public function activePrizesForPeriod(RafflePeriod $period)
    {
        $prizes = $this->redemptionService->getActivePrizesForPeriod($period);

        return response()->json([
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'exchange_window' => [
                    'start' => $period->exchange_start_at?->toDateTimeString() ?? $period->end_at->toDateTimeString(),
                    'end' => $period->exchange_end_at?->toDateTimeString() ?? $period->end_at->toDateTimeString(),
                ],
            ],
            'prizes' => $prizes,
        ]);
    }

    public function customerPurchases(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:raffle_periods,id'],
        ]);

        $purchases = $customer->purchases()
            ->where('raffle_period_id', $validated['period_id'])
            ->notRedeemed()
            ->with(['tenant', 'paymentType'])
            ->latest('purchased_at')
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'receipt_number' => $purchase->receipt_number,
                'purchased_at' => $purchase->purchased_at->format('d M Y H:i'),
                'amount' => $purchase->amount,
                'tenant_name' => $purchase->tenant->name,
                'payment_type_name' => $purchase->paymentType?->name,
            ]);

        return response()->json(['purchases' => $purchases]);
    }

    // ---------------------------------------------------------------------
    // API: penukaran poin (1 struk = 1 hadiah)
    // ---------------------------------------------------------------------
    public function store(StorePointRedemptionRequest $request)
    {
        $validated = $request->validated();

        $customer = Customer::findOrFail($validated['customer_id']);
        $prize = Prize::findOrFail($validated['prize_id']);

        try {
            [$redemption, $purchase] = DB::transaction(function () use ($validated, $customer, $prize) {
                $purchase = isset($validated['purchase_id'])
                    ? Purchase::findOrFail($validated['purchase_id'])
                    : Purchase::create([
                        'raffle_period_id' => $validated['period_id'],
                        'customer_id' => $customer->id,
                        'tenant_id' => $validated['tenant_id'],
                        'entered_by' => auth()->id(),
                        'receipt_number' => $validated['receipt_number'],
                        'purchased_at' => $validated['purchased_at'],
                        'amount' => $validated['amount'],
                        'payment_type_id' => $validated['payment_type_id'] ?? null,
                        'exchange_status' => 'belum',
                        'notes' => $validated['notes'] ?? null,
                    ]);

                $redemption = $this->redemptionService->redeem(
                    $customer,
                    $purchase,
                    $prize,
                    $validated['cs_id'] ?? null,
                    $validated['notes'] ?? null,
                );

                return [$redemption, $purchase];
            });

            $period = $purchase->rafflePeriod;
            $calc = $this->calculator->calculatePointsForPurchase($purchase, $prize, $period);

            $bonusInfo = null;
            if ($purchase->payment_type_id && $calc['bonus_points'] > 0) {
                $bonusRule = BonusPointRule::findActiveForPeriodAndPaymentType(
                    $period->id,
                    $purchase->payment_type_id,
                );
                if ($bonusRule) {
                    $bonusInfo = [
                        'payment_type_name' => $bonusRule->paymentType->name,
                        'payment_code' => $bonusRule->paymentType->code,
                        'bonus_points' => $bonusRule->bonus_poin,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Penukaran poin berhasil: {$calc['total_points']} poin untuk {$prize->name}.",
                'redemption' => [
                    'id' => $redemption->id,
                    'customer_id' => $redemption->customer_id,
                    'period_id' => $redemption->raffle_period_id,
                    'prize_id' => $redemption->prize_id,
                    'purchase_id' => $redemption->purchase_id,
                    'cs_id' => $redemption->cs_id,
                    'redeemed_at' => $redemption->redeemed_at->toDateTimeString(),
                    'nominal_struk' => $redemption->nominal_struk,
                    'total_poin' => $redemption->total_poin_didapat,
                    'status' => $redemption->status,
                ],
                'calculation' => [
                    'nominal_per_poin' => $calc['nominal_per_poin'],
                    'points_from_amount' => $calc['points_from_amount'],
                    'bonus_points' => $calc['bonus_points'],
                    'total_points' => $calc['total_points'],
                    'unused_remainder' => $calc['unused_remainder'],
                ],
                'bonus' => $bonusInfo,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ---------------------------------------------------------------------
    // API: saldo poin customer per hadiah
    // ---------------------------------------------------------------------
    public function customerBalances(Request $request, $customerId)
    {
        $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);

        $customer = Customer::findOrFail($customerId);

        $activePeriod = RafflePeriod::where('status', 'active')
            ->where('drawing_status', '!=', 'completed')
            ->first();

        if (! $activePeriod) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                ],
                'period' => null,
                'balances' => [],
                'message' => 'Tidak ada periode aktif saat ini.',
            ]);
        }

        $balances = $this->redemptionService->getCustomerPointBalances($customer, $activePeriod);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ],
            'period' => [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
            ],
            'balances' => $balances,
        ]);
    }

    // ---------------------------------------------------------------------
    // VIEW: history penukaran poin (DataTables)
    // ---------------------------------------------------------------------
    public function history(Request $request)
    {

        $authUser = $request->user();
        if (! $authUser->can('manage-prizes') && ! $authUser->can('manage-users')) {
            abort(403, 'Unauthorized');
        }

        if ($request->wantsJson()) {
            $query = PointRedemption::with(['customer', 'prize', 'purchase', 'cs'])
                ->latest('point_redemptions.created_at');

            return DataTables::eloquent($query)
                ->addColumn('customer_name', fn (PointRedemption $r) => $r->customer->name)
                ->addColumn('prize_name', fn (PointRedemption $r) => $r->prize->name)
                ->addColumn('receipt_number', fn (PointRedemption $r) => $r->purchase->receipt_number)
                ->addColumn('cs_name', fn (PointRedemption $r) => $r->cs ? $r->cs->name : '-')
                ->addColumn('redeemed_at_formatted', fn (PointRedemption $r) => $r->redeemed_at->format('d M Y H:i'))
                ->addColumn('actions', fn (PointRedemption $r) => [
                    'show_url' => route('admin.point-exchange.show', $r),
                ])
                ->make(true);
        }

        $periods = RafflePeriod::query()
            ->where('status', 'active')
            ->where('drawing_status', '!=', 'completed')
            ->orderByDesc('start_at')
            ->get(['id', 'name']);
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        $tenants = Tenant::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
        $paymentTypes = PaymentType::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.point-exchange.index', compact('periods', 'customers', 'tenants', 'paymentTypes'));
    }

    // ---------------------------------------------------------------------
    // VIEW: detail satu transaksi penukaran
    // ---------------------------------------------------------------------
    public function show(PointRedemption $redemption)
    {
        $authUser = request()->user();
        if (! $authUser->can('manage-prizes') && ! $authUser->can('manage-users')) {
            abort(403, 'Unauthorized');
        }

        $redemption->load(['customer', 'prize', 'purchase', 'cs', 'rafflePeriod']);

        return view('admin.point-exchange.show', compact('redemption'));
    }
}
