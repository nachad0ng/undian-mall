@extends('layouts.app')
@section('title', 'Detail Tipe Pembayaran')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">{{ $paymentType->name }}</h2>
                        <div class="text-secondary font-monospace">{{ $paymentType->code }}</div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.payment-types.edit', $paymentType) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Code</div>
                                <div class="datagrid-content font-monospace">{{ $paymentType->code }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Name</div>
                                <div class="datagrid-content">{{ $paymentType->name }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Description</div>
                                <div class="datagrid-content">{{ $paymentType->description ?: '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Status</div>
                                <div class="datagrid-content">
                                    @if($paymentType->is_active)
                                        <span class="badge bg-success-lt">Active</span>
                                    @else
                                        <span class="badge bg-warning-lt">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Dibuat</div>
                                <div class="datagrid-content">{{ $paymentType->created_at->format('d M Y H:i:s') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Diperbarui</div>
                                <div class="datagrid-content">{{ $paymentType->updated_at->format('d M Y H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transaksi yang Menggunakan Tipe Ini</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Struk</th>
                                    <th>Customer</th>
                                    <th>Periode</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paymentType->purchases()->with(['customer', 'rafflePeriod'])->latest()->take(10)->get() as $purchase)
                                    <tr>
                                        <td class="font-monospace">{{ $purchase->receipt_number }}</td>
                                        <td>{{ $purchase->customer?->name ?? '-' }}</td>
                                        <td>{{ $purchase->rafflePeriod?->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($purchase->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">Belum ada transaksi yang menggunakan tipe pembayaran ini.</td>
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
