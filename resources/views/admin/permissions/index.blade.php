@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manage Permissions</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Create Permission
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <form method="GET" action="{{ route('admin.permissions.index') }}" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control d-inline-block" style="max-width: 200px;" placeholder="Search permission..." value="{{ $search }}">
                                <button type="submit" class="btn btn-outline-primary">Search</button>
                                @if ($search)
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Permission Name</th>
                                <th>Roles Using This</th>
                                <th>Created At</th>
                                <th class="w-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="text-truncate">
                                            <strong>{{ $permission->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($permission->roles->count() > 0)
                                            <div>
                                                @foreach ($permission->roles->take(2) as $role)
                                                    <span class="badge bg-green">{{ $role->name }}</span>
                                                @endforeach
                                                @if ($permission->roles->count() > 2)
                                                    <span class="badge bg-gray">+{{ $permission->roles->count() - 2 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-gray">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $permission->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-icon btn-ghost-primary" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                                    <path d="M16 5l3 3"/>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-icon btn-ghost-danger" title="Delete" onclick="confirmDelete('{{ route('admin.permissions.destroy', $permission) }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <line x1="4" y1="7" x2="20" y2="7"/>
                                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                                        <path d="M9 7v-1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v1"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; width: 48px; height: 48px; opacity: 0.5;">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M19 6.364l1.636 -1.636a2 2 0 0 1 2.828 2.828l-.001 .001l-1.636 1.636m-2.182 -2.182a2 2 0 0 1 2.182 2.182v4m-6 2a2 2 0 0 1 -2 -2v-4a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-4"/>
                                            <path d="M9 11v-5a2 2 0 0 0 -2 -2h-4a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h4"/>
                                        </svg>
                                        <p>No permissions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($permissions->hasPages())
                    <div class="card-footer">
                        {{ $permissions->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
