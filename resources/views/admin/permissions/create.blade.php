@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Create New Permission</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.permissions.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Permission Name *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. view-reports" required>
                                    <small class="form-hint">Use kebab-case format (lowercase with hyphens)</small>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-footer">
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-link">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Create Permission</button>
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
