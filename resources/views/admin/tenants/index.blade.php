@extends('layouts.app')

@section('title', 'Tenant')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Tenant</h2>
                        <div class="text-secondary mt-1">Kelola toko / penyewa mall yang dapat menerbitkan struk undian</div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Tenant
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <form class="d-flex flex-wrap gap-2 w-100" onsubmit="return false;">
                            <input type="text" id="filter-search" class="form-control" style="max-width: 250px;"
                                placeholder="Cari nama / kode / unit...">
                            <select id="filter-status" class="form-select" style="max-width: 150px;">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <button type="button" id="filter-apply" class="btn btn-outline-primary">Filter</button>
                            <button type="button" id="filter-reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="tenants-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th>Kode & Nama</th>
                                    <th>Unit</th>
                                    <th>Telepon</th>
                                    <th>Transaksi</th>
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

            const table = $('#tenants-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: @json(route('admin.tenants.index')),
                    data: function(data) {
                        data.custom_search = $search.val();
                        data.status = $status.val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'name',
                        render: (data) =>
                            `<div class="fw-medium">${data.name}</div>
                            <div class="text-secondary small font-monospace">${data.code}</div>`
                    },
                    {
                        data: 'unit_number',
                        name: 'unit_number',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'purchases_count',
                        name: 'purchases_count',
                        render: (data) => `<span class="badge bg-purple-lt">${data} Struk</span>`
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: (data) => data === 'active' ?
                            '<span class="badge bg-success-lt">Aktif</span>' :
                            '<span class="badge bg-warning-lt">Tidak Aktif</span>'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data) =>
                            `<div class="btn-group">
                                <a href="${data.show_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="${data.edit_url}" class="btn btn-sm btn-icon btn-ghost-info" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-warning btn-toggle" data-url="${data.toggle_url}" title="${data.toggle_title}">
                                    <i class="bi bi-power"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete" data-url="${data.delete_url}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>`
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data tenant.',
                    processing: 'Memuat...'
                },

                createdRow: function(row, data, dataIndex) {
                    var url = @json(route('admin.tenants.show', '/')) + '/' + data.id;
                    DataTableClickableRow(row, data, url);
                },
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

            $('#tenants-table').on('click', '.btn-toggle', function() {
                $.post($(this).data('url'), {
                    _token: csrfToken
                }).done(() => table.ajax.reload(null, false));
            });

            $('#tenants-table').on('click', '.btn-delete', function() {

                if (!confirm('Yakin ingin menghapus tenant ini?')) return;
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
                    error: (xhr) => alert(xhr.responseJSON?.message || 'Gagal menghapus tenant.')
                });
            });
        });
    </script>
@endpush
