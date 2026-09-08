<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $query = Tenant::withCount('purchases')->orderBy('name');

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('custom_search')) {
                $search = $request->input('custom_search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('unit_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            return DataTables::eloquent($query)
                ->addColumn('actions', fn ($tenant) => [
                    'show_url' => route('admin.tenants.show', $tenant),
                    'edit_url' => route('admin.tenants.edit', $tenant),
                    'toggle_url' => route('admin.tenants.toggle-status', $tenant),
                    'delete_url' => route('admin.tenants.destroy', $tenant),
                    'toggle_title' => $tenant->status === 'active' ? 'Nonaktifkan' : 'Aktifkan',
                ])
                ->make(true);
        }

        return view('admin.tenants.index');
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(StoreTenantRequest $request)
    {
        $validated = $request->validated();

        $tenant = Tenant::create($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil ditambahkan.");
    }

    public function show(Tenant $tenant)
    {
        $tenant->loadCount('purchases');
        $recentPurchases = $tenant->purchases()->with(['customer', 'rafflePeriod'])->latest()->take(10)->get();

        return view('admin.tenants.show', [
            'tenant' => $tenant,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $validated = $request->validated();

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil diperbarui.");
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->purchases()->exists()) {
            return back()->with('error', "Tenant '{$tenant->name}' tidak dapat dihapus karena memiliki riwayat transaksi.");
        }

        $name = $tenant->name;
        $tenant->delete();

        $message = "Tenant '{$name}' berhasil dihapus.";

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->route('admin.tenants.index')->with('success', $message);
    }

    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        $tenant->update(['status' => $newStatus]);

        return back()->with('success', "Status tenant '{$tenant->name}' berhasil diubah menjadi {$newStatus}.");
    }
}
