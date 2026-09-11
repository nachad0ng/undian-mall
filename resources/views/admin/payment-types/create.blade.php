@extends('layouts.app')
@section('title', 'Tambah Tipe Pembayaran')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3">
                    <div class="col">
                        <h2 class="page-title">Tambah Tipe Pembayaran</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.payment-types.store') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required">Code</label>
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                            placeholder="mis. KARTU_MEGA" value="{{ old('code') }}" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-hint">Use uppercase letters, no spaces. Example: KARTU_MEGA, TUNAI</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required">Name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="3">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required">Status</label>
                                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                                            <option value="1" @selected(old('is_active', 1) == 1)>Active</option>
                                            <option value="0" @selected(old('is_active', 1) == 0)>Inactive</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-footer">
                                        <a href="{{ route('admin.payment-types.index') }}" class="btn btn-link">Batal</a>
                                        <button type="submit" class="btn btn-primary">Simpan Tipe Pembayaran</button>
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
