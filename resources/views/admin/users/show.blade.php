@extends('layouts.app')
@section('title', 'Detail User')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">{{ $user->name }}</h2>
                        <div class="text-secondary">{{ $user->email }}</div>
                    </div>
                    <div class="col-auto"><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit</a> <a
                            href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Telepon</div>
                                <div class="datagrid-content">{{ $user->phone ?: '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Role</div>
                                <div class="datagrid-content">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Status</div>
                                <div class="datagrid-content">{{ $user->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Alamat</div>
                                <div class="datagrid-content">{{ $user->address ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
