@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Edit User: {{ $user->name }}</h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf @method('PUT')
                            @include('admin.users._form', ['user' => $user])
                            <div class="form-footer"><a href="{{ route('admin.users.index') }}"
                                    class="btn btn-link">Batal</a><button type="submit" class="btn btn-primary">Perbarui
                                    User</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
