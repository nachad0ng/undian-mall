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
                                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="customer_id">Customer</label>
                                <select id="customer_id" class="form-select" required>
                                    <option value="">Pilih customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
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
                                    <input id="amount" class="form-control" type="number" min="1" required
                                        placeholder="0">
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

            function showAlert(message, type = 'danger') {
                $('#redemption-alert').removeClass('d-none alert-danger alert-success').addClass(`alert-${type}`)
                    .text(message);
            }

            function updatePreview() {
                const prize = prizes.find((item) => String(item.id) === prizeSelect.val());
                const amount = Number($('#amount').val());
                if (!amount || !prize) {
                    preview.text('Pilih struk dan hadiah');
                    submitButton.prop('disabled', true);
                    return;
                }
                const points = Math.floor(amount / prize.nominal_per_poin);
                preview.text(`${points} poin dari Rp ${amount.toLocaleString('id-ID')}`);
                submitButton.prop('disabled', false);
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
            $('#amount').add(prizeSelect).on('input change', updatePreview);
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
                        amount: $('#amount').val(),
                        payment_type_id: $('#payment_type_id').val(),
                        prize_id: prizeSelect.val(),
                        notes: $('#notes').val()
                    }
                }).done(function(response) {
                    showAlert(response.message, 'success');
                    $('#redemption-form')[0].reset();
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
