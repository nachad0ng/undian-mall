@extends('layouts.app')

@section('title', 'Tambah Tenant')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Tambah Tenant</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.tenants.store') }}">
                                @csrf
                                @include('admin.tenants._form')
                                <div class="form-footer">
                                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Tenant</button>
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
