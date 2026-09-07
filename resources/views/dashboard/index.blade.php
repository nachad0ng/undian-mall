@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Page body -->
<div class="page-wrapper">
    <div class="page-body">        
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="alert alert-important alert-info" role="alert">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                    <path d="M12 9v2m0 4v2"/>
                                </svg>
                                <h3 class="alert-title">Selamat datang, {{ $user->name }}!</h3>
                                <div class="text-secondary">
                                    <p>Sistem manajemen undian berhadiah mall siap digunakan.</p>
                                    @if ($roles->isNotEmpty())
                                        <p><strong>Role Anda:</strong> {{ $roles->implode(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards mt-2">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary text-truncate text-uppercase" style="font-size: .75rem">Total Customer</div>
                            <div class="text-reset">
                                <div class="display-6">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary text-truncate text-uppercase" style="font-size: .75rem">Total Tenant</div>
                            <div class="text-reset">
                                <div class="display-6">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary text-truncate text-uppercase" style="font-size: .75rem">Total Kupon</div>
                            <div class="text-reset">
                                <div class="display-6">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary text-truncate text-uppercase" style="font-size: .75rem">Total Pemenang</div>
                            <div class="text-reset">
                                <div class="display-6">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards mt-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Sistem</h3>
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
        </div>
    </div>
</div>
@endsection
