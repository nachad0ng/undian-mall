@extends('layouts.app')
@section('title', 'Detail Penukaran Poin')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Detail Penukaran Poin</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.point-exchange.history') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Customer</div>
                                <div class="datagrid-content">{{ $redemption->customer->name }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Struk</div>
                                <div class="datagrid-content">{{ $redemption->purchase->receipt_number }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Hadiah</div>
                                <div class="datagrid-content">{{ $redemption->prize->name }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Nominal Struk</div>
                                <div class="datagrid-content">Rp {{ number_format($redemption->nominal_struk, 0, ',', '.') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Poin Didapat</div>
                                <div class="datagrid-content">{{ $redemption->total_poin_didapat }} poin</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Petugas</div>
                                <div class="datagrid-content">{{ $redemption->cs?->name ?? '-' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Waktu Tukar</div>
                                <div class="datagrid-content">{{ $redemption->redeemed_at->format('d M Y H:i:s') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Status</div>
                                <div class="datagrid-content">
                                    @if($redemption->status === 'success')
                                        <span class="badge bg-success-lt">Sukses</span>
                                    @else
                                        <span class="badge bg-danger-lt">{{ ucfirst($redemption->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($redemption->notes)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Catatan</div>
                                    <div class="datagrid-content">{{ $redemption->notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detail Perhitungan Poin</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th> Komponen</th>
                                    <th> Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Nominal Per Poin</td>
                                    <td>Rp {{ number_format($redemption->nominal_struk, 0, ',', '.') }} / poin (rule hadiah)</td>
                                </tr>
                                <tr>
                                    <td>Poin Dari Nominal</td>
                                    <td>{{ floor($redemption->nominal_struk / $redemption->total_poin_didapat > 0 ? $redemption->nominal_struk / $redemption->total_poin_didapat : 0) }} (estimasi)</td>
                                </tr>
                                <tr>
                                    <td>Bonus Pembayaran</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Poin</strong></td>
                                    <td><strong>{{ $redemption->total_poin_didapat }} poin</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
