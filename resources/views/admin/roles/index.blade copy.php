@extends('layouts.app')

@section('title', 'Manage Roles')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manage Roles</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Create Role
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <form method="GET" action="{{ route('admin.roles.index') }}" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control d-inline-block" style="max-width: 200px;" placeholder="Search role..." value="{{ $search }}">
                                <button type="submit" class="btn btn-outline-primary">Search</button>
                                @if ($search)
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Permissions</th>
                                <th>Created At</th>
                                <th class="w-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr>
                                    <td>
                                        <div class="text-truncate">
                                            <strong>{{ $role->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue">{{ $role->permissions->count() }} permissions</span>
                                    </td>
                                    <td>
                                        {{ $role->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-icon btn-ghost-primary" title="Edit">                                                
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="button" 
                                                    class="btn btn-icon btn-ghost-danger" 
                                                    title="Delete" 
                                                    onclick="confirmDelete(event)">
                                                    <i class="bi bi-trash"></i>
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
                                        <p>No roles found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($roles->hasPages())
                    <div class="card-footer">
                        {{ $roles->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
