@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="page-wrapper">
    <div class="page-body">                    
        <div class="container-xl">
            <div class="row mb-3">
                <div class="col">
                    <h2 class="page-title">Edit Permission: {{ $permission->name }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">Permission Name *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $permission->name) }}" required>
                                    <small class="form-hint">Use kebab-case format (lowercase with hyphens)</small>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-footer">
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-link">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Permission</button>
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
