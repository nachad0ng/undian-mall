@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Laporan & Export</h2>
                        <div class="text-secondary mt-1">Unduh laporan sistem dalam format Excel (.xlsx).</div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Laporan Poin Redemption</h3>
                        <div class="card-title-description text-secondary">
                            Semua transaksi penukaran struk menjadi poin.
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.point-redemptions.export') }}" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="period_id_pr">Periode</label>
                                <select class="form-select" id="period_id_pr" name="period_id">
                                    <option value="">Semua periode</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="from_pr">Dari</label>
                                <input class="form-control" id="from_pr" name="from" type="date" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="to_pr">Sampai</label>
                                <input class="form-control" id="to_pr" name="to" type="date" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100" type="submit">
                                    <i class="bi bi-download me-1"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Laporan Saldo Poin Customer</h3>
                        <div class="card-title-description text-secondary">
                            Saldo poin tiap customer per periode per hadiah.
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.customer-point-balances.export') }}" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label" for="period_id_cb">Periode / Hadiah</label>
                                <select class="form-select" id="period_id_cb" name="period_id">
                                    <option value="">Semua periode</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="prize_id_cb">Hadiah (opsional)</label>
                                <select class="form-select" id="prize_id_cb" name="prize_id">
                                    <option value="">Semua hadiah</option>
                                    @foreach ($prizes as $prize)
                                        <option value="{{ $prize->id }}" @selected(request('prize_id') == $prize->id)>{{ $prize->name }} ({{ $prize->rafflePeriod?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100" type="submit">
                                    <i class="bi bi-download me-1"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Laporan Pemenang Undian</h3>
                        <div class="card-title-description text-secondary">
                            Daftar pemenang drawing per hadiah.
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.winners.export') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="period_id_w">Periode</label>
                                <select class="form-select" id="period_id_w" name="period_id">
                                    <option value="">Semua periode</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="from_w">Dari</label>
                                <input class="form-control" id="from_w" name="from" type="date" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="to_w">Sampai</label>
                                <input class="form-control" id="to_w" name="to" type="date" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <select class="form-select" name="published">
                                    <option value="">Semua status</option>
                                    <option value="published" @selected(request('published') == 'published')>Sudah Dipublikasikan</option>
                                    <option value="not_published" @selected(request('published') == 'not_published')>Belum Dipublikasikan</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-success" type="submit">
                                    <i class="bi bi-download me-1"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Laporan Audit Log</h3>
                        <div class="card-title-description text-secondary">
                            Riwayat semua aksi redemption, drawing, dan publish/unpublish.
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.audit-logs.export') }}" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="action_al">Action</label>
                                <select class="form-select" id="action_al" name="action">
                                    <option value="">Semua action</option>
                                    @foreach ($auditActions as $action)
                                        <option value="{{ $action }}" @selected(request('action') == $action)>{{ $action }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="from_al">Dari</label>
                                <input class="form-control" id="from_al" name="from" type="date" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="to_al">Sampai</label>
                                <input class="form-control" id="to_al" name="to" type="date" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100" type="submit">
                                    <i class="bi bi-download me-1"></i> Export Excel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
