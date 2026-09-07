<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
        
            $query = Role::withCount('permissions');

            return DataTables::eloquent($query)
                ->addColumn('permissions_badge', function ($role) {
                    return $role->permissions_count;
                })
                ->addColumn('actions', function ($role) {
                    return [
                        'edit_url'   => route('admin.roles.edit', $role),
                        'delete_url' => route('admin.roles.destroy', $role),
                    ];
                })
                ->editColumn('created_at', fn($role) => $role->created_at->format('d M Y H:i'))
                //->rawColumns([]) // tidak perlu raw karena render di JS
                ->make(true);
        }

        return view('admin.roles.index');
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('admin.roles.create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Create role
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Sync permissions
            if (!empty($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', "Role '{$role->name}' berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Role '{$validated['name']}' gagal dibuat: " . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        // Update role
        $role->update([
            'name' => $validated['name'],
        ]);

        // Sync permissions
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {

        // Prevent deleting system roles
        if (in_array($role->name, [
            'Super Admin', 
            'Manager', 
            'Customer Service', 
            'Auditor'
        ])) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menghapus role system '{$role->name}'.",
            ], 422);
        }

        $roleName = $role->name;
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => "Role '{$roleName}' berhasil dihapus.",
        ]);
    }
}
