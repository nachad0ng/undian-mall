@extends('layouts.app')
@section('title', 'Edit Bonus Poin')
@section('content')<div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Edit Bonus Poin</h2>
                    </div>
                    <div class="col-auto"><a href="{{ route('admin.bonus-point-rules.index') }}"
                            class="btn btn-outline-secondary">Kembali</a></div>
                </div>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST"
                                    action="{{ route('admin.bonus-point-rules.update', $bonusPointRule) }}">
                                    @method('PUT')@include('admin.bonus-point-rules._form')<div class="form-footer"><a
                                            href="{{ route('admin.bonus-point-rules.index') }}"
                                            class="btn btn-link">Batal</a><button class="btn btn-primary">Simpan
                                            Perubahan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>@endsection
