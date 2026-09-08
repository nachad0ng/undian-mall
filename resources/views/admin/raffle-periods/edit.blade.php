@extends('layouts.app')

@section('title', 'Edit Periode Undian')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Edit Periode: {{ $period->name }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.raffle-periods.update', $period) }}">
                                @csrf
                                @method('PUT')
                                @include('admin.raffle-periods._form', ['period' => $period])

                                <div class="form-footer">
                                    <a href="{{ route('admin.raffle-periods.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Perbarui Periode</button>
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
