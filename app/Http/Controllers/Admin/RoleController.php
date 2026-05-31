<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage-roles');
    }

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $roles = $query->paginate(10);

        return view('admin.roles.index', [
            'roles' => $roles,
            'search' => $request->input('search', ''),
        ]);
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

        // Create role
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        // Sync permissions
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' berhasil dibuat.");
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
        if (in_array($role->name, ['Super Admin', 'Manager', 'Customer Service', 'Auditor'])) {
            return back()->with('error', "Tidak dapat menghapus role system '{$role->name}'.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' berhasil dihapus.");
    }
}
