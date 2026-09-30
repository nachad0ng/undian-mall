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
                        <a href="{{ route('admin.point-exchange.print', $redemption) }}" target="_blank"
                            class="btn btn-primary"><i class="bi bi-printer me-1"></i>Cetak Nomor</a>
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
                                <div class="datagrid-title">Tipe Pembayaran</div>
                                <div class="datagrid-content">
                                    {{ $pointDetails['payment_type_name'] ?? '-' }}
                                    @if ($pointDetails['payment_type_code'])
                                        <span class="text-secondary">({{ $pointDetails['payment_type_code'] }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Nominal Struk</div>
                                <div class="datagrid-content">Rp
                                    {{ number_format($redemption->nominal_struk, 0, ',', '.') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Poin Didapat</div>
                                <div class="datagrid-content">{{ $redemption->total_poin_didapat }} poin
                                    ({{ $redemption->raffleTickets->count() }} nomor undian)</div>
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
                                    @if ($redemption->status === 'success')
                                        <span class="badge bg-success-lt">Sukses</span>
                                    @else
                                        <span class="badge bg-danger-lt">{{ ucfirst($redemption->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($redemption->notes)
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
                        <h3 class="card-title">Nomor Undian ({{ $redemption->raffleTickets->count() }})</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Sequence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($redemption->raffleTickets as $ticket)
                                    <tr>
                                        <td><span class="font-monospace fw-bold">{{ $ticket->ticket_number }}</span></td>
                                        <td>{{ $ticket->sequence_number }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary">Belum ada nomor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
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
                                    <td>Rp {{ number_format($pointDetails['nominal_per_poin'], 0, ',', '.') }} / poin</td>
                                </tr>
                                <tr>
                                    <td>Poin Dari Nominal Struk</td>
                                    <td>{{ $pointDetails['poin_dari_nominal'] }} poin</td>
                                </tr>
                                <tr>
                                    <td>Bonus Dari Tipe Pembayaran</td>
                                    <td>{{ $pointDetails['poin_bonus_pembayaran'] }} poin
                                        @if (($pointDetails['bonus_mode'] ?? null) === 'multiply' && $pointDetails['bonus_multiplier'])
                                            <span class="badge bg-purple-lt">{{ rtrim(rtrim($pointDetails['bonus_multiplier'], '0'), '.') }}x
                                                lipat</span>
                                        @elseif(($pointDetails['bonus_mode'] ?? null) === 'add' && $pointDetails['poin_bonus_pembayaran'])
                                            <span class="badge bg-blue-lt">+{{ $pointDetails['poin_bonus_pembayaran'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rule Bonus</td>
                                    <td>{{ $pointDetails['bonus_rule_id'] ? '#' . $pointDetails['bonus_rule_id'] : '-' }}
                                    </td>
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
