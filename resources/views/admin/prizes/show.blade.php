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
                        @can('manage-draws')
                            <button type="button" class="btn btn-success" id="draw-prize-btn"
                                data-preview-url="{{ route('admin.prizes.draw-preview', $prize) }}"
                                data-draw-url="{{ route('admin.prizes.draw', $prize) }}"
                                {{ $prize->isUsedInDrawing() ? 'disabled' : '' }}>
                                <i class="bi bi-shuffle me-1"></i> Undi Hadiah
                            </button>
                        @endcan
                        <a href="{{ route('admin.prizes.edit', $prize) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('admin.prizes.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>

                @can('manage-draws')
                    <div class="card mb-3 border-success" id="draw-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Pengundian Berbasis Poin</h3>
                                <div class="text-secondary">Setiap poin menjadi satu bobot peluang.</div>
                            </div>
                            <span class="badge bg-secondary-lt" id="draw-status">Memuat pool...</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Total poin pool</div>
                                    <div class="h2 mb-0" id="draw-pool-count">-</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Customer eligible</div>
                                    <div class="h2 mb-0" id="draw-eligible-count">-</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Kuota pemenang</div>
                                    <div class="h2 mb-0" id="draw-quantity">{{ $prize->quantity }}</div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3 mb-0 d-none" id="draw-message"></div>
                        </div>
                    </div>
                @endcan

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
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    @can('manage-draws')
                                        <th class="text-end">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prize->winners as $winner)
                                    <tr>
                                        <td>{{ $winner->customer?->name ?? '-' }}</td>
                                        <td>{{ $winner->won_at?->format('d M Y H:i') }}</td>
                                        <td>
                                            @if ($winner->is_published)
                                                <span class="badge bg-success-lt">Dipublikasikan</span>
                                            @else
                                                <span class="badge bg-secondary-lt">Draft</span>
                                            @endif
                                        </td>
                                        @can('manage-draws')
                                            <td class="text-end">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-{{ $winner->is_published ? 'warning' : 'success' }} winner-toggle"
                                                    data-url="{{ route($winner->is_published ? 'admin.winners.unpublish' : 'admin.winners.publish', $winner) }}">
                                                    <i
                                                        class="bi bi-{{ $winner->is_published ? 'eye-slash' : 'globe2' }} me-1"></i>
                                                    {{ $winner->is_published ? 'Sembunyikan' : 'Publikasikan' }}
                                                </button>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->can('manage-draws') ? 4 : 3 }}"
                                            class="text-center text-secondary py-4">Belum ada pemenang.</td>
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

@can('manage-draws')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                const drawButton = document.getElementById('draw-prize-btn');
                const status = document.getElementById('draw-status');
                const message = document.getElementById('draw-message');
                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                if (!drawButton) return;

                const preview = await fetch(drawButton.dataset.previewUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(response => response.json());

                document.getElementById('draw-pool-count').textContent = preview.pool_count;
                document.getElementById('draw-eligible-count').textContent = preview.eligible_customers;
                document.getElementById('draw-quantity').textContent = preview.quantity;
                status.textContent = preview.can_draw ? 'Siap diundi' : 'Belum siap';
                status.className = `badge ${preview.can_draw ? 'bg-success-lt' : 'bg-warning-lt'}`;
                drawButton.disabled = !preview.can_draw;

                if (preview.reason) {
                    message.textContent = preview.reason;
                    message.classList.remove('d-none');
                }

                drawButton.addEventListener('click', async function() {
                    if (!confirm('Undi hadiah ini sekarang? Proses tidak dapat diulang.')) return;

                    drawButton.disabled = true;
                    const response = await fetch(drawButton.dataset.drawUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        alert(result.message || 'Pengundian gagal.');
                        drawButton.disabled = false;
                        return;
                    }

                    alert(result.message);
                    window.location.reload();
                });

                document.querySelectorAll('.winner-toggle').forEach(button => {
                    button.addEventListener('click', async function() {
                        const response = await fetch(button.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        const result = await response.json();
                        if (!response.ok) return alert(result.message ||
                            'Gagal memperbarui publikasi.');
                        window.location.reload();
                    });
                });
            });
        </script>
    @endpush
@endcan
