@extends('layouts.app')

@section('title', 'Tambah Hadiah')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Tambah Hadiah</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.prizes.store') }}">
                                @csrf
                                @include('admin.prizes._form')
                                <div class="form-footer">
                                    <a href="{{ route('admin.prizes.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Hadiah</button>
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
