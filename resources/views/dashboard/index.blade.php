@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title">
                <i class="bi bi-house-fill"></i> Dashboard
            </div>
        </div>
    </div>

    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">Selamat datang, {{ $user->name }}!</h4>
        <p>Sistem manajemen undian berhadiah mall siap digunakan.</p>
        @if ($roles->isNotEmpty())
            <hr>
            <p class="mb-0"><strong>Role Anda:</strong> {{ $roles->implode(', ') }}</p>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title text-muted">Total Customer</h6>
                            <h2 class="text-primary">0</h2>
                        </div>
                        <i class="bi bi-people-fill text-primary" style="font-size: 2rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title text-muted">Total Tenant</h6>
                            <h2 class="text-success">0</h2>
                        </div>
                        <i class="bi bi-building text-success" style="font-size: 2rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title text-muted">Total Kupon</h6>
                            <h2 class="text-warning">0</h2>
                        </div>
                        <i class="bi bi-ticket-perforated text-warning" style="font-size: 2rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title text-muted">Total Pemenang</h6>
                            <h2 class="text-danger">0</h2>
                        </div>
                        <i class="bi bi-award text-danger" style="font-size: 2rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informasi Sistem</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-success">Aktif</span></p>
                            <p><strong>Versi Laravel:</strong> 11.x</p>
                            <p><strong>Database:</strong> MySQL</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Aplikasi:</strong> Mall Lucky Draw Management System</p>
                            <p><strong>Template:</strong> Tabler.io + Bootstrap 5</p>
                            <p><strong>Tanggal Akses:</strong> {{ now()->format('d F Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
