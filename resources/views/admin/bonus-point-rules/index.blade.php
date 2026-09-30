@extends('layouts.app')
@section('title', 'Bonus Poin Pembayaran')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Bonus Poin Pembayaran</h2>
                        <div class="text-secondary mt-1">Atur bonus poin per tipe pembayaran untuk setiap periode.</div>
                    </div>
                    <div class="col-auto"><a href="{{ route('admin.bonus-point-rules.create') }}" class="btn btn-primary"><i
                                class="bi bi-plus-lg me-1"></i> Tambah Bonus</a></div>
                </div>
                <form class="row g-2 mb-3" method="GET">
                    <div class="col-md-5"><select name="raffle_period_id" class="form-select">
                            <option value="">Semua periode</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected(request('raffle_period_id') == $period->id)>{{ $period->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>
                </form>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Tipe Pembayaran</th>
                                    <th>Mode Bonus</th>
                                    <th>Status</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rules as $rule)
                                    <tr>
                                        <td>{{ $rule->rafflePeriod?->name ?? '-' }}</td>
                                        <td><strong>{{ $rule->paymentType?->name ?? '-' }}</strong><br><span
                                                class="text-secondary">{{ $rule->paymentType?->code }}</span></td>
                                        <td>
                                            @if ($rule->mode === 'multiply')
                                                <span class="badge bg-purple-lt">{{ $rule->describe() }}</span>
                                            @else
                                                <span class="badge bg-blue-lt">+{{ $rule->bonus_poin }} poin</span>
                                            @endif
                                        </td>
                                        <td><span
                                                class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}-lt">{{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap"><a
                                                    href="{{ route('admin.bonus-point-rules.edit', $rule) }}"
                                                    class="btn btn-sm btn-icon btn-ghost-primary" title="Edit"><i
                                                        class="bi bi-pencil-square"></i></a>
                                                <form method="POST"
                                                    action="{{ route('admin.bonus-point-rules.toggle-status', $rule) }}">
                                                    @csrf<button class="btn btn-sm btn-icon btn-ghost-warning"
                                                        title="Ubah status"><i class="bi bi-power"></i></button></form>
                                                <form method="POST"
                                                    action="{{ route('admin.bonus-point-rules.destroy', $rule) }}"
                                                    onsubmit="return confirm('Hapus bonus poin ini?')">@csrf
                                                    @method('DELETE')<button class="btn btn-sm btn-icon btn-ghost-danger"
                                                        title="Hapus"><i class="bi bi-trash"></i></button></form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty<tr>
                                        <td colspan="5" class="text-center text-secondary py-4">Belum ada bonus poin.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $rules->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
