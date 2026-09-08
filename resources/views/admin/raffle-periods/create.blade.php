@extends('layouts.app')

@section('title', 'Tambah Periode Undian')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Tambah Periode Undian</h2>
                    <div class="text-secondary mt-1">Periode wajib memiliki aturan kupon sebelum dapat menerima transaksi.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.raffle-periods.store') }}">
                                @csrf
                                @include('admin.raffle-periods._form')

                                <div class="form-footer">
                                    <a href="{{ route('admin.raffle-periods.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Periode</button>
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
