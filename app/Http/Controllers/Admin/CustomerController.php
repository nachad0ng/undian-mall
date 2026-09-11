<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = Customer::query()
                ->withCount(['purchases', 'winners', 'pointRedemptions'])
                ->latest('id');

            if ($request->filled('custom_search')) {
                $search = $request->input('custom_search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            return DataTables::eloquent($query)
                ->addColumn('actions', fn ($customer) => [
                    'show_url' => route('admin.customers.show', $customer),
                    'edit_url' => route('admin.customers.edit', $customer),
                    'delete_url' => route('admin.customers.destroy', $customer),
                ])
                ->make(true);
        }

        return view('admin.customers.index');
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('admin.customers.index')
            ->with('success', "Pelanggan '{$customer->name}' berhasil ditambahkan.");
    }

    public function show(Customer $customer)
    {
        $customer->loadCount(['purchases', 'winners', 'pointRedemptions', 'pointBalances']);
        $recentPurchases = $customer->purchases()
            ->with(['tenant', 'rafflePeriod'])
            ->latest('purchased_at')
            ->take(10)
            ->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('admin.customers.index')
            ->with('success', "Pelanggan '{$customer->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, Customer $customer)
    {
        $hasRelatedRecords = $customer->purchases()->exists()
            || $customer->winners()->exists()
            || $customer->pointRedemptions()->exists()
            || $customer->pointBalances()->exists();

        if ($hasRelatedRecords) {
            return back()->with('error', "Pelanggan '{$customer->name}' tidak dapat dihapus karena memiliki riwayat transaksi atau poin.");
        }

        $name = $customer->name;
        $customer->delete();
        $message = "Pelanggan '{$name}' berhasil dihapus.";

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->route('admin.customers.index')->with('success', $message);
    }
}
