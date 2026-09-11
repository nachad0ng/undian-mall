@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Tambah Pelanggan</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.customers.store') }}">
                                    @csrf
                                    @include('admin.customers._form')
                                    <div class="form-footer">
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-link">Batal</a>
                                        <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
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
