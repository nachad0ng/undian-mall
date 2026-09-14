@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Laporan</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
        @if (auth()->user()->can('view-reports') || auth()->user()->can('manage-prizes') || auth()->user()->can('manage-users'))
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Poin Redemption</h5>
                        <p class="card-text text-muted">Riwayat transaksi penukaran poin customer.</p>
                        <a href="{{ route('admin.reports.point-redemptions.index') }}" class="btn btn-primary">Lihat Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-wallet fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Saldo Poin Customer</h5>
                        <p class="card-text text-muted">Daftar saldo poin per customer per periode hadiah.</p>
                        <a href="{{ route('admin.reports.customer-point-balances.index') }}" class="btn btn-primary">Lihat Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Pemenang Undian</h5>
                        <p class="card-text text-muted">Daftar pemenang undian yang berhasil didraw.</p>
                        <a href="{{ route('admin.reports.winners.index') }}" class="btn btn-primary">Lihat Laporan</a>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->can('view-audit-logs'))
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Audit Log</h5>
                    <p class="card-text text-muted">Riwayat log audit sistem.</p>
                    <a href="{{ route('admin.reports.audit-logs.index') }}" class="btn btn-primary">Lihat Laporan</a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
