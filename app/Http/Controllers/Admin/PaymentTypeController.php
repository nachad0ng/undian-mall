<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTypeRequest;
use App\Http\Requests\UpdatePaymentTypeRequest;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = PaymentType::query()->withCount('purchases')->orderBy('name');

            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->input('status') === 'active');
            }

            return DataTables::eloquent($query)
                ->addColumn('actions', fn ($pt) => [
                    'show_url' => route('admin.payment-types.show', $pt),
                    'edit_url' => route('admin.payment-types.edit', $pt),
                    'toggle_url' => route('admin.payment-types.toggle-status', $pt),
                    'delete_url' => route('admin.payment-types.destroy', $pt),
                    'toggle_title' => $pt->is_active ? 'Nonaktifkan' : 'Aktifkan',
                ])
                ->addColumn('status_badge', fn ($pt) => $pt->is_active
                        ? '<span class="badge bg-success-lt">Active</span>'
                        : '<span class="badge bg-boom-lt">Inactive</span>')
                ->rawColumns(['status_badge'])
                ->make(true);
        }

        return view('admin.payment-types.index');
    }

    public function create()
    {
        return view('admin.payment-types.create');
    }

    public function store(StorePaymentTypeRequest $request)
    {
        $validated = $request->validated();
        $pt = PaymentType::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Payment type '{$pt->name}' created."], 201);
        }

        return redirect()->route('admin.payment-types.index')
            ->with('success', "Payment type '{$pt->name}' created.");
    }

    public function show(PaymentType $paymentType)
    {
        $paymentType->loadCount('purchases');
        $recentPurchases = $paymentType->purchases()
            ->with(['customer', 'rafflePeriod'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.payment-types.show', [
            'paymentType' => $paymentType,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function edit(PaymentType $paymentType)
    {
        return view('admin.payment-types.edit', [
            'paymentType' => $paymentType,
        ]);
    }

    public function update(UpdatePaymentTypeRequest $request, PaymentType $paymentType)
    {
        $validated = $request->validated();
        $paymentType->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Payment type '{$paymentType->name}' updated."]);
        }

        return redirect()->route('admin.payment-types.index')
            ->with('success', "Payment type '{$paymentType->name}' updated.");
    }

    public function destroy(Request $request, PaymentType $paymentType)
    {
        if ($paymentType->purchases()->exists()) {
            return back()->with('error', "Payment type '{$paymentType->name}' cannot be deleted because it has associated purchases.");
        }

        $name = $paymentType->name;
        $paymentType->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Payment type '{$name}' deleted."]);
        }

        return redirect()->route('admin.payment-types.index')
            ->with('success', "Payment type '{$name}' deleted.");
    }

    public function toggleStatus(PaymentType $paymentType)
    {
        $newStatus = $paymentType->is_active ? false : true;
        $paymentType->update(['is_active' => $newStatus]);

        return back()->with('success', "Payment type '{$paymentType->name}' status changed to ".($newStatus ? 'active' : 'inactive').'.');
    }
}
