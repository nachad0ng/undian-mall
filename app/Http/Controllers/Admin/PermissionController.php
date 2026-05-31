<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage-permissions');
    }

    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $permissions = $query->paginate(10);

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'search' => $request->input('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        $validated = $request->validated();

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$validated['name']}' berhasil dibuat.");
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $validated = $request->validated();

        $permission->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permission->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        // Prevent deleting system permissions
        $systemPermissions = [
            'view-dashboard',
            'manage-users',
            'manage-roles',
            'manage-permissions',
        ];

        if (in_array($permission->name, $systemPermissions)) {
            return back()->with('error', "Tidak dapat menghapus permission system '{$permission->name}'.");
        }

        $permissionName = $permission->name;
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permissionName}' berhasil dihapus.");
    }
}
