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
                                <h3 class="card-title">Pengundian Otomatis Berbobot Poin</h3>
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
                    <div class="card mb-3 border-primary" id="manual-draw-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Pengundian Manual Bola ({{ $prize->ticket_digits }} digit)</h3>
                                <div class="text-secondary">Ambil bola 0–9 sebanyak {{ $prize->ticket_digits }}x, lalu cocokkan nomor.</div>
                            </div>
                            <span class="badge bg-secondary-lt" id="manual-status">Memuat...</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Nomor diterbitkan</div>
                                    <div class="h2 mb-0" id="manual-ticket-count">-</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Pemenang / Kuota</div>
                                    <div class="h2 mb-0"><span id="manual-winner-count">-</span> / {{ $prize->quantity }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-secondary small">Sisa slot</div>
                                    <div class="h2 mb-0" id="manual-remaining">-</div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-end" id="manual-inputs">
                                @for ($i = 0; $i < $prize->ticket_digits; $i++)
                                    <div class="col" style="max-width: 110px">
                                        <label class="form-label">Bola {{ $i + 1 }}</label>
                                        <select class="form-select manual-digit" data-pos="{{ $i }}">
                                            @for ($d = 0; $d <= 9; $d++)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                @endfor
                                <div class="col-auto">
                                    <label class="form-label">Nomor</label>
                                    <div class="form-control fw-bold font-monospace" id="manual-number-preview">
                                        {{ str_repeat('0', $prize->ticket_digits) }}</div>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-secondary" id="manual-lookup-btn"
                                        data-url="{{ route('admin.prizes.ticket-lookup', ['prize' => $prize, 'ticketNumber' => '__NUM__']) }}">Cek
                                        Pemilik</button>
                                    <button type="button" class="btn btn-primary" id="manual-draw-btn"
                                        data-preview-url="{{ route('admin.prizes.manual-preview', $prize) }}"
                                        data-draw-url="{{ route('admin.prizes.manual-draw', $prize) }}">Catat
                                        Pemenang</button>
                                </div>
                            </div>
                            <div class="alert mt-3 mb-0 d-none" id="manual-message"></div>
                            <div class="text-secondary small mt-2" id="manual-owner"></div>
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
                                <div class="datagrid-title">Digit Nomor Undian</div>
                                <div class="datagrid-content">{{ $prize->ticket_digits }} digit</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Nomor Diterbitkan</div>
                                <div class="datagrid-content">{{ $prize->raffle_tickets_count }} (terakhir:
                                    {{ str_pad((string) $prize->ticket_counter, $prize->ticket_digits, '0', STR_PAD_LEFT) }})
                                </div>
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
                                    <th>Nomor Undian</th>
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
                                        <td><span class="font-monospace fw-bold">{{ $winner->winning_number ?? $winner->raffleTicket?->ticket_number ?? '-' }}</span></td>
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
                                        <td colspan="{{ auth()->user()->can('manage-draws') ? 5 : 4 }}"
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

                const manualDrawBtn = document.getElementById('manual-draw-btn');
                const manualLookupBtn = document.getElementById('manual-lookup-btn');
                const manualStatus = document.getElementById('manual-status');
                const manualMessage = document.getElementById('manual-message');
                const manualOwner = document.getElementById('manual-owner');
                const manualPreview = document.getElementById('manual-number-preview');

                function currentNumber() {
                    return [...document.querySelectorAll('.manual-digit')].map(el => el.value).join('');
                }

                function refreshPreview() {
                    if (manualPreview) manualPreview.textContent = currentNumber();
                }
                document.querySelectorAll('.manual-digit').forEach(el => el.addEventListener('change', refreshPreview));
                refreshPreview();

                function showManual(msg, type = 'warning') {
                    manualMessage.textContent = msg;
                    manualMessage.className = `alert mt-3 mb-0 alert-${type}`;
                    manualMessage.classList.remove('d-none');
                }

                if (manualDrawBtn) {
                    const mPreview = await fetch(manualDrawBtn.dataset.previewUrl, {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json());

                    document.getElementById('manual-ticket-count').textContent = mPreview.ticket_count;
                    document.getElementById('manual-winner-count').textContent = mPreview.winner_count;
                    document.getElementById('manual-remaining').textContent = mPreview.remaining;
                    manualStatus.textContent = mPreview.can_draw ? 'Siap' : 'Belum siap';
                    manualStatus.className = `badge ${mPreview.can_draw ? 'bg-success-lt' : 'bg-warning-lt'}`;
                    manualDrawBtn.disabled = !mPreview.can_draw;
                    if (mPreview.reason) showManual(mPreview.reason);

                    manualLookupBtn.addEventListener('click', async function() {
                        const num = currentNumber();
                        manualOwner.textContent = 'Mengecek...';
                        const url = manualLookupBtn.dataset.url.replace('__NUM__', num);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        manualOwner.textContent = res.ok
                            ? `Nomor ${data.ticket_number} milik ${data.customer_name}${data.already_won ? ' (SUDAH MENANG)' : ''}`
                            : (data.message || 'Nomor tidak terdaftar.');
                    });

                    manualDrawBtn.addEventListener('click', async function() {
                        const num = currentNumber();
                        if (!confirm(`Catat nomor ${num} sebagai pemenang?`)) return;
                        manualDrawBtn.disabled = true;
                        const response = await fetch(manualDrawBtn.dataset.drawUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({ winning_number: num })
                        });
                        const result = await response.json();
                        if (!response.ok) {
                            showManual(result.message || 'Gagal mencatat pemenang.', 'danger');
                            manualDrawBtn.disabled = false;
                            return;
                        }
                        alert(result.message);
                        window.location.reload();
                    });
                }
            });
        </script>
    @endpush
@endcan
