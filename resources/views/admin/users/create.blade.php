@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Tambah User</h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf
                            @include('admin.users._form')
                            <div class="form-footer"><a href="{{ route('admin.users.index') }}"
                                    class="btn btn-link">Batal</a><button type="submit" class="btn btn-primary">Simpan
                                    User</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
