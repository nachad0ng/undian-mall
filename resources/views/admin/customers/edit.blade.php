@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Edit Pelanggan: {{ $customer->name }}</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                                    @csrf
                                    @method('PUT')
                                    @include('admin.customers._form', ['customer' => $customer])
                                    <div class="form-footer">
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-link">Batal</a>
                                        <button type="submit" class="btn btn-primary">Perbarui Pelanggan</button>
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
