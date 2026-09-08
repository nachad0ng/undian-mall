@extends('layouts.app')

@section('title', 'Edit Hadiah')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Edit Hadiah: {{ $prize->name }}</h2>
                    @if ($isUsedInDrawing)
                        <div class="text-secondary mt-1">Hadiah ini sudah digunakan dalam pengundian. Beberapa field dikunci.</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.prizes.update', $prize) }}">
                                @csrf
                                @method('PUT')
                                @include('admin.prizes._form', ['prize' => $prize, 'isUsedInDrawing' => $isUsedInDrawing])
                                <div class="form-footer">
                                    <a href="{{ route('admin.prizes.index') }}" class="btn btn-link">Batal</a>
                                    <button type="submit" class="btn btn-primary">Perbarui Hadiah</button>
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
