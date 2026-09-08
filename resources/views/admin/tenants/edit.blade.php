@extends('layouts.app')

@section('title', 'Edit Tenant')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Edit Tenant: {{ $tenant->name }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
                                @csrf
                                @method('PUT')
                                @include('admin.tenants._form', ['tenant' => $tenant])
                                <div class="form-footer">
                                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Perbarui Tenant</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
