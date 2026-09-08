@extends('layouts.app')

@section('title', 'Periode Undian')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Periode Undian</h2>
                        <div class="text-secondary mt-1">Kelola program periode undian belanja mall dan aturan kupon</div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.raffle-periods.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Periode
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <form class="d-flex flex-wrap gap-2 w-100" onsubmit="return false;">
                            <input type="text" id="filter-search" class="form-control" style="max-width: 250px;"
                                placeholder="Cari nama / kode...">

                            <select id="filter-status" class="form-select" style="max-width: 150px;">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="draft">Draft</option>
                                <option value="closed">Ditutup</option>
                            </select>

                            <button type="button" id="filter-apply" class="btn btn-outline-primary">Filter</button>
                            <button type="button" id="filter-reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="raffle-periods-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th>Kode & Nama Periode</th>
                                    <th>Rentang Tanggal</th>
                                    <th>Aturan Kupon</th>
                                    <th>Statistik</th>
                                    <th>Status</th>
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
        document.addEventListener('DOMContentLoaded', function() {
            const $search = $('#filter-search');
            const $status = $('#filter-status');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const formatDate = (date) => new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }).format(new Date(date));
            const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(value);

            const table = $('#raffle-periods-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: @json(route('admin.raffle-periods.index')),
                    data: function(data) {
                        data.custom_search = $search.val();
                        data.status = $status.val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'name',
                        render: (data) => `<div class="fw-medium">${data.name}</div>
                            <div class="text-secondary small font-monospace">${data.code}</div>`
                    },
                    {
                        data: null,
                        name: 'start_at',
                        render: (data) => `<div>${formatDate(data.start_at)}</div>
                            <div class="text-secondary small">s/d ${formatDate(data.end_at)}</div>`
                    },
                    {
                        data: null,
                        name: 'purchase_threshold',
                        render: (data) => `<div class="small">Min. Belanja: <strong>Rp ${formatCurrency(data.purchase_threshold)}</strong></div>
                            <div class="text-secondary small">Per Kupon: Rp ${formatCurrency(data.coupon_unit)}</div>`
                    },
                    {
                        data: null,
                        name: 'prizes_count',
                        orderable: false,
                        render: (data) => `<span class="badge bg-blue-lt me-1">${data.prizes_count} Hadiah</span>
                            <span class="badge bg-purple-lt me-1">${data.purchases_count} Struk</span>
                            <span class="badge bg-green-lt">${data.coupons_count} Kupon</span>`
                    },
                    {
                        data: null,
                        name: 'status',
                        render: (data) => {
                            const statuses = {
                                active: ['success', 'Aktif'],
                                inactive: ['warning', 'Tidak Aktif'],
                                closed: ['secondary', 'Ditutup'],
                                draft: ['gray', 'Draft']
                            };
                            const [color, label] = statuses[data.status] || statuses.draft;
                            const drawing = data.drawing_status === 'completed' ?
                                '<span class="badge bg-info-lt mt-1 d-block">Selesai Diundi</span>' :
                                '';

                            return `<span class="badge bg-${color}-lt">${label}</span>${drawing}`;
                        }
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data) => `<div class="btn-group">
                            <a href="${data.show_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="${data.edit_url}" class="btn btn-sm btn-icon btn-ghost-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <button type="button" class="btn btn-sm btn-icon btn-ghost-warning btn-toggle" data-url="${data.toggle_url}" title="${data.toggle_title}"><i class="bi bi-power"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete" data-url="${data.delete_url}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>`
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data periode undian.',
                    processing: 'Memuat...'
                },
                createdRow: function(row, data) {
                    const url = @json(route('admin.raffle-periods.show', '/')) + '/' + data.id;
                    DataTableClickableRow(row, data, url);
                }
            });

            $('#filter-apply').on('click', () => table.ajax.reload());
            $search.on('keypress', (event) => {
                if (event.which === 13) table.ajax.reload();
            });
            $('#filter-reset').on('click', () => {
                $search.val('');
                $status.val('');
                table.ajax.reload();
            });

            $('#raffle-periods-table').on('click', '.btn-toggle', function() {
                $.post($(this).data('url'), {
                    _token: csrfToken
                }).done(() => table.ajax.reload(null, false));
            });

            $('#raffle-periods-table').on('click', '.btn-delete', function() {
                if (!confirm('Yakin ingin menghapus periode undian ini?')) return;

                $.ajax({
                    url: $(this).data('url'),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        _method: 'DELETE'
                    },
                    success: () => table.ajax.reload(null, false),
                    error: (xhr) => alert(xhr.responseJSON?.message ||
                        'Gagal menghapus periode undian.')
                });
            });
        });
    </script>
@endpush
