@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Pelanggan</h2>
                        <div class="text-secondary mt-1">Kelola data pelanggan dan riwayat transaksinya</div>
                    </div>
                    <div class="col-auto"><a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i
                                class="bi bi-plus-lg me-1"></i> Tambah Pelanggan</a></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <form class="d-flex flex-wrap gap-2 w-100" onsubmit="return false;">
                            <input type="text" id="filter-search" class="form-control" style="max-width: 300px;"
                                placeholder="Cari nama / telepon / identitas...">
                            <button type="button" id="filter-apply" class="btn btn-outline-primary">Filter</button>
                            <button type="button" id="filter-reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table id="customers-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Transaksi</th>
                                    <th>Poin</th>
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
            const $search = $('#filter-search');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const table = $('#customers-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: @json(route('admin.customers.index')),
                    data: data => data.custom_search = $search.val()
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        render: data => `<div class="fw-medium">${data}</div>`
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: data => data || '-'
                    },
                    {
                        data: 'purchases_count',
                        name: 'purchases_count',
                        render: data => `<span class="badge bg-purple-lt">${data} Struk</span>`
                    },
                    {
                        data: 'point_redemptions_count',
                        name: 'point_redemptions_count',
                        render: data => data
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: data => `<div class="btn-group">
                            <a href="${data.show_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="${data.edit_url}" class="btn btn-sm btn-icon btn-ghost-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete" data-url="${data.delete_url}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>`
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data pelanggan.',
                    processing: 'Memuat...'
                },
                createdRow: function(row, data) {
                    const url = @json(route('admin.customers.show', '/')) + '/' + data.id;
                    DataTableClickableRow(row, data, url);
                }
            });
            $('#filter-apply').on('click', () => table.ajax.reload());
            $search.on('keypress', event => {
                if (event.which === 13) table.ajax.reload();
            });
            $('#filter-reset').on('click', () => {
                $search.val('');
                table.ajax.reload();
            });
            $('#customers-table').on('click', '.btn-delete', function() {
                if (!confirm('Yakin ingin menghapus pelanggan ini?')) return;
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
                    error: xhr => alert(xhr.responseJSON?.message || 'Gagal menghapus pelanggan.')
                });
            });
        });
    </script>
@endpush
