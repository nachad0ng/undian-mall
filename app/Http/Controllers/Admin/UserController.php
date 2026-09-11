<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $query = User::with('roles')->latest();

            return DataTables::eloquent($query)
                ->addColumn('role_name', fn (User $user) => $user->roles->pluck('name')->join(', ') ?: '-')
                ->addColumn('actions', fn (User $user) => [
                    'show_url' => route('admin.users.show', $user),
                    'edit_url' => route('admin.users.edit', $user),
                    'delete_url' => route('admin.users.destroy', $user),
                    'is_current' => $user->is(auth()->user()),
                ])
                ->editColumn('status', fn (User $user) => $user->status === 'active' ? 'Aktif' : 'Tidak Aktif')
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create', ['roles' => $this->roles()]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $role = $validated['role'];
        unset($validated['role']);

        $user = User::create($validated);
        $user->assignRole($role);

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();
        $role = $validated['role'];
        unset($validated['role']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return response()->json(['success' => false, 'message' => 'User yang sedang login tidak dapat dihapus.'], 422);
        }

        $name = $user->name;
        $user->delete();

        return response()->json(['success' => true, 'message' => "User '{$name}' berhasil dihapus."]);
    }

    private function roles()
    {
        return Role::where('guard_name', 'web')->orderBy('name')->get();
    }
}
