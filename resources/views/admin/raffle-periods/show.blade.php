@extends('layouts.app')

@section('title', 'Detail Periode Undian')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">{{ $period->name }}</h2>
                    <div class="text-secondary font-monospace">{{ $period->code }}</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.raffle-periods.edit', $period) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('admin.raffle-periods.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Periode</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Rentang</div>
                                    <div class="datagrid-content">{{ $period->start_at->format('d M Y H:i') }} — {{ $period->end_at->format('d M Y H:i') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Status</div>
                                    <div class="datagrid-content">{{ $period->status }} / drawing: {{ $period->drawing_status }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Bisa Terima Transaksi</div>
                                    <div class="datagrid-content">{{ $period->canAcceptTransactions() ? 'Ya' : 'Tidak' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Deskripsi</div>
                                    <div class="datagrid-content">{{ $period->description ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Aturan Kupon</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Minimum Belanja</div>
                                    <div class="datagrid-content">Rp {{ number_format($period->purchase_threshold, 0, ',', '.') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Kelipatan per Kupon</div>
                                    <div class="datagrid-content">Rp {{ number_format($period->coupon_unit, 0, ',', '.') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Maks. Kupon / Transaksi</div>
                                    <div class="datagrid-content">{{ $period->max_coupon_per_transaction ?? 'Tidak dibatasi' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Hadiah Periode</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Urutan</th>
                                <th>Nama</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prizes as $prize)
                                <tr>
                                    <td>{{ $prize->sequence }}</td>
                                    <td>
                                        <a href="{{ route('admin.prizes.show', $prize) }}">{{ $prize->name }}</a>
                                    </td>
                                    <td>{{ $prize->quantity }}</td>
                                    <td>{{ $prize->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary text-center py-4">Belum ada hadiah pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
