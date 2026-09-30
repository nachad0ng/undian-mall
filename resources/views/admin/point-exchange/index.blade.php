@extends('layouts.app')
@section('title', 'Point Exchange')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Point Exchange</h2>
                        <div class="text-secondary mt-1">Penukaran struk menjadi poin hadiah</div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Input Struk & Tukar Poin</h3>
                    </div>
                    <div class="card-body">
                        <form id="redemption-form" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="period_id">Periode</label>
                                <select id="period_id" class="form-select" required>
                                    <option value="">Pilih periode</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}" @selected($activePeriod?->id === $period->id)>{{ $period->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="customer_id">Customer</label>
                                <div class="input-group ">
                                    <select id="customer_id" class="form-select" required>
                                        <option value="">Pilih customer</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#quick-customer-modal" title="Tambah customer">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required" for="receipt_number">Nomor struk</label>
                                <input id="receipt_number" class="form-control" maxlength="100" required
                                    placeholder="Contoh: INV-001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required" for="tenant_id">Tenant</label>
                                <select id="tenant_id" class="form-select" required>
                                    <option value="">Pilih tenant</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required" for="purchased_at">Tanggal belanja</label>
                                <input id="purchased_at" class="form-control" type="datetime-local" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required" for="amount">Nominal struk</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="amount" class="form-control" type="text" inputmode="decimal" required
                                        placeholder="0.00" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="payment_type_id">Tipe pembayaran</label>
                                <select id="payment_type_id" class="form-select">
                                    <option value="">Pilih tipe pembayaran</option>
                                    @foreach ($paymentTypes as $paymentType)
                                        <option value="{{ $paymentType->id }}">{{ $paymentType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="prize_id">Hadiah</label>
                                <select id="prize_id" class="form-select" required disabled>
                                    <option value="">Pilih periode terlebih dahulu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preview poin</label>
                                <div id="points-preview" class="form-control bg-light">Pilih struk dan hadiah</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Catatan</label>
                                <textarea id="notes" class="form-control" rows="2" maxlength="1000"></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button id="submit-redemption" class="btn btn-primary" type="submit" disabled>
                                    <i class="bi bi-arrow-left-right me-1"></i> Proses Penukaran
                                </button>
                            </div>
                        </form>
                        <div id="redemption-alert" class="alert d-none mt-3 mb-0"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Riwayat Penukaran Poin</h3>
                            <a class="btn btn-success" href="{{ route('admin.reports.point-redemptions.index') }}">
                                <i class="bi bi-download me-1"></i>Laporan Export
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table" class="table table-vcenter card-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Struk</th>
                                    <th>Hadiah</th>
                                    <th>Nominal Struk</th>
                                    <th>Poin Didapat</th>
                                    <th>Petugas</th>
                                    <th>Waktu</th>
                                    <th class="w-1 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="quick-customer-modal" tabindex="-1" aria-labelledby="quick-customer-title"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="quick-customer-form">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="quick-customer-title">Tambah customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="customer-alert" class="alert alert-danger d-none"></div>
                                    <div class="mb-3">
                                        <label class="form-label required" for="quick-customer-name">Nama</label>
                                        <input id="quick-customer-name" name="name" class="form-control" required
                                            maxlength="255" autocomplete="name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required" for="quick-customer-phone">Nomor
                                            telepon</label>
                                        <input id="quick-customer-phone" name="phone" class="form-control" required
                                            maxlength="50" inputmode="tel" autocomplete="tel">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required">Nomor Identitas</label>
                                        <input type="text" name="identity_number" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                                    <button id="save-quick-customer" type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>Simpan dan pilih
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.plugins.datatables', ['ajax_same_page' => true])
    <script>
        window.addEventListener('load', function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const periodSelect = $('#period_id');
            const customerSelect = $('#customer_id');
            const prizeSelect = $('#prize_id');
            const submitButton = $('#submit-redemption');
            const preview = $('#points-preview');
            let prizes = [];
            const amountMask = IMask(document.getElementById('amount'), {
                mask: Number,
                thousandsSeparator: ',',
                radix: '.',
                mapToRadix: ['.'],
                scale: 2,
                padFractionalZeros: true,
                normalizeZeros: true,
                min: 0,
            });

            customerSelect.select2({
                allowClear: true,
                placeholder: 'Cari nama atau nomor telepon...',
                minimumInputLength: 1,
                ajax: {
                    url: @json(route('admin.customers.search')),
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({
                        q: params.term
                    }),
                    processResults: (response) => ({
                        results: response.results
                    }),
                },
            });

            function amountValue() {
                const value = Number(amountMask.unmaskedValue);
                return Number.isFinite(value) ? value : 0;
            }

            amountMask.on('accept', updatePreview);


            function showAlert(message, type = 'danger') {
                $('#redemption-alert').removeClass('d-none alert-danger alert-success').addClass(`alert-${type}`)
                    .text(message);
            }

            function updatePreview() {
                const prize = prizes.find((item) => String(item.id) === prizeSelect.val());
                const amount = amountValue();
                if (!amount || !prize) {
                    preview.text('Pilih struk dan hadiah');
                    submitButton.prop('disabled', true);
                    return;
                }
                // Hitung cepat sisi klien, lalu sinkronkan bonus via server (debounce).
                const base = Math.floor(amount / prize.nominal_per_poin);
                preview.text(`${base} poin dari Rp ${amount.toLocaleString('id-ID')} (menghitung bonus...)`);
                submitButton.prop('disabled', base < 1);
                queueServerPreview();
            }

            let previewTimer = null;
            let previewSeq = 0;

            function queueServerPreview() {
                clearTimeout(previewTimer);
                previewTimer = setTimeout(fetchServerPreview, 400);
            }

            function fetchServerPreview() {
                const prizeId = prizeSelect.val();
                const periodId = periodSelect.val();
                const amount = Math.round(amountValue());
                const paymentTypeId = $('#payment_type_id').val();
                if (!prizeId || !periodId || !amount) return;
                const seq = ++previewSeq;
                $.ajax({
                    url: @json(route('admin.point-exchange.preview')),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        period_id: periodId,
                        prize_id: prizeId,
                        amount: amount,
                        payment_type_id: paymentTypeId || null,
                    },
                }).done(function(response) {
                    if (seq !== previewSeq) return;
                    const c = response.calculation;
                    let text =
                        `${c.points_from_amount} poin nominal + ${c.bonus_points} bonus = ${c.total_points} poin (${c.ticket_count} nomor undian)`;
                    if (response.bonus) {
                        text += ` — ${response.bonus.payment_type_name} (${response.bonus.label})`;
                    }
                    preview.text(text);
                    submitButton.prop('disabled', c.total_points < 1);
                }).fail(function(xhr) {
                    if (seq !== previewSeq) return;
                    preview.text(xhr.responseJSON?.message || 'Preview gagal dihitung.');
                    submitButton.prop('disabled', true);
                });
            }

            function loadPrizes() {
                const periodId = periodSelect.val();
                prizeSelect.prop('disabled', true).html('<option value="">Memuat hadiah...</option>');
                if (!periodId) return;
                $.get(@json(url('/admin/periods')) + `/${periodId}/active-prizes`, function(response) {
                    prizes = response.prizes;
                    prizeSelect.html('<option value="">Pilih hadiah</option>' + prizes.map((prize) =>
                        `<option value="${prize.id}">${prize.name} (Rp ${Number(prize.nominal_per_poin).toLocaleString('id-ID')}/poin)</option>`
                    ).join(''));
                    prizeSelect.prop('disabled', false);
                    updatePreview();
                });
            }

            periodSelect.on('change', function() {
                loadPrizes();
            });
            $('#amount').on('input', function() {
                updatePreview();
            });
            $('#amount').on('blur', function() {
                this.value = formatAmount(this.value);
                updatePreview();
            });
            prizeSelect.on('change', updatePreview);
            $('#payment_type_id').on('change', updatePreview);
            $('#quick-customer-modal').on('shown.bs.modal', () => $('#quick-customer-name').trigger('focus'));
            $('#quick-customer-form').on('submit', function(event) {
                event.preventDefault();
                const saveButton = $('#save-quick-customer').prop('disabled', true);
                $('#customer-alert').addClass('d-none');
                $.ajax({
                    url: @json(route('admin.customers.quick-store')),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json'
                    },
                    data: $(this).serialize(),
                }).done(function(response) {
                    const option = new Option(response.customer.text, response.customer.id, true,
                        true);

                    customerSelect.append(option).trigger('change');

                    bootstrap.Modal.getOrCreateInstance(document.getElementById(
                        'quick-customer-modal')).hide();
                    $('#quick-customer-form')[0].reset();
                }).fail(function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const message = Object.values(errors).flat()[0] || xhr.responseJSON?.message ||
                        'Customer gagal ditambahkan.';
                    $('#customer-alert').removeClass('d-none').text(message);
                }).always(function() {
                    saveButton.prop('disabled', false);
                });
            });

            $('#purchased_at').val(new Date().toISOString().slice(0, 16));
            loadPrizes();

            $('#redemption-form').on('submit', function(event) {
                event.preventDefault();
                submitButton.prop('disabled', true);
                $.ajax({
                    url: @json(route('admin.point-exchange.store')),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        period_id: periodSelect.val(),
                        customer_id: customerSelect.val(),
                        receipt_number: $('#receipt_number').val(),
                        tenant_id: $('#tenant_id').val(),
                        purchased_at: $('#purchased_at').val(),
                        amount: Math.round(amountValue()),
                        payment_type_id: $('#payment_type_id').val(),
                        prize_id: prizeSelect.val(),
                        notes: $('#notes').val()
                    }
                }).done(function(response) {
                    const tickets = (response.tickets || []).map((t) => t.ticket_number).join(', ');
                    const calc = response.calculation || {};
                    let detail =
                        `${calc.points_from_amount ?? ''} poin nominal + ${calc.bonus_points ?? 0} bonus = ${calc.total_points ?? ''} poin`;
                    if (response.bonus) {
                        detail += ` (${response.bonus.payment_type_name}: ${response.bonus.label})`;
                    }
                    showAlert(`${response.message} ${detail}. Nomor: ${tickets}`, 'success');
                    if (response.redemption?.print_url) {
                        window.open(response.redemption.print_url, '_blank');
                    }
                    $('#redemption-form')[0].reset();
                    periodSelect.val(@json($activePeriod?->id));
                    customerSelect.val(null).trigger('change');
                    $('#purchased_at').val(new Date().toISOString().slice(0, 16));
                    loadPrizes();
                    $('#amount').val('');
                    prizeSelect.prop('disabled', true).html(
                        '<option value="">Pilih periode terlebih dahulu</option>');
                    preview.text('Pilih struk dan hadiah');
                    table.ajax.reload(null, false);
                }).fail(function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'Penukaran struk gagal diproses.');
                    submitButton.prop('disabled', false);
                });
            });

            const table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('admin.point-exchange.history'))
                },
                columns: [{
                        data: 'customer_name',
                        name: 'customer.name',
                        render: (data) => `<div class="fw-medium">${data}</div>`
                    },
                    {
                        data: 'receipt_number',
                        name: 'purchase.receipt_number',
                        render: (data) => `<span class="font-monospace text-small">${data}</span>`
                    },
                    {
                        data: 'prize_name',
                        name: 'prize.name'
                    },
                    {
                        data: 'nominal_struk',
                        name: 'nominal_struk',
                        render: (data) => 'Rp ' + Number(data).toLocaleString('id-ID')
                    },
                    {
                        data: 'total_poin_didapat',
                        name: 'total_poin_didapat',
                        render: (data) => `<span class="badge bg-primary-lt">${data} poin</span>`
                    },
                    {
                        data: 'cs_name',
                        name: 'cs.name',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'redeemed_at_formatted',
                        name: 'redeemed_at',
                        render: (data) => data
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return `<div class="btn-group">
                                <a href="${data.show_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="${data.print_url}" target="_blank" class="btn btn-sm btn-icon btn-ghost-secondary" title="Cetak nomor">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>`;
                        }
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data penukaran poin.',
                    processing: 'Memuat...'
                }
            });
        });
    </script>
@endpush
