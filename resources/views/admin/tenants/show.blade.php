@extends('layouts.app')

@section('title', 'Detail Tenant')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">{{ $tenant->name }}</h2>
                    <div class="text-secondary font-monospace">{{ $tenant->code }}</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Unit</div>
                            <div class="datagrid-content">{{ $tenant->unit_number ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Telepon</div>
                            <div class="datagrid-content">{{ $tenant->phone ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">{{ $tenant->isActive() ? 'Aktif' : 'Tidak Aktif' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Jumlah Transaksi</div>
                            <div class="datagrid-content">{{ $tenant->purchases_count }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transaksi Terbaru</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Struk</th>
                                <th>Pelanggan</th>
                                <th>Periode</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPurchases as $purchase)
                                <tr>
                                    <td class="font-monospace">{{ $purchase->receipt_number }}</td>
                                    <td>{{ $purchase->customer?->name ?? '-' }}</td>
                                    <td>{{ $purchase->rafflePeriod?->name ?? '-' }}</td>
                                    <td>Rp {{ number_format($purchase->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">Belum ada transaksi.</td>
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
