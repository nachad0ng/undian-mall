@extends('layouts.app')

@section('title', 'Detail Hadiah')

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">{{ $prize->name }}</h2>
                    <div class="text-secondary">{{ $prize->rafflePeriod?->name }}</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.prizes.edit', $prize) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('admin.prizes.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Urutan</div>
                            <div class="datagrid-content">{{ $prize->sequence }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Jumlah</div>
                            <div class="datagrid-content">{{ $prize->quantity }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">{{ $prize->status }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Sudah Diundi</div>
                            <div class="datagrid-content">{{ $prize->isUsedInDrawing() ? 'Ya' : 'Belum' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Deskripsi</div>
                            <div class="datagrid-content">{{ $prize->description ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pemenang</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Kupon</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prize->winners as $winner)
                                <tr>
                                    <td>{{ $winner->customer?->name ?? '-' }}</td>
                                    <td class="font-monospace">{{ $winner->coupon?->coupon_number ?? '-' }}</td>
                                    <td>{{ $winner->won_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada pemenang.</td>
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
