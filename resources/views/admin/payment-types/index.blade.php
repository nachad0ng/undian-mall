@extends('layouts.app')
@section('title', 'Payment Types')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Payment Types</h2>
                        <div class="text-secondary mt-1">Kelola tipe pembayaran untuk bonus poin</div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.payment-types.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Pembayaran
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table id="table" class="table table-vcenter card-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
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
        window.addEventListener('load', function() {
            const table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('admin.payment-types.index')),
                },
                columns: [{
                        data: 'code',
                        name: 'code',
                        render: (data) => data
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
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
                                <a href="${data.edit_url}" class="btn btn-sm btn-icon btn-ghost-info" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-warning btn-toggle"
                                    data-url="${data.toggle_url}" title="${data.toggle_title}">
                                    <i class="bi bi-power"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete"
                                    data-url="${data.delete_url}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>`;
                        }
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data tipe pembayaran.',
                    processing: 'Memuat...'
                },
                createdRow: function(row, data, dataIndex) {
                    var url = @json(route('admin.payment-types.show', '/')) + '/' + data.id;
                    DataTableClickableRow(row, data, url);
                },
            });

            $('#table').on('click', '.btn-toggle', function() {
                $.post($(this).data('url'), {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(() => table.ajax.reload(null, false));
            });

            $('#table').on('click', '.btn-delete', function() {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus tipe pembayaran ini? Tipe pembayaran yang memiliki transaksi tidak dapat dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: $(this).data('url'),
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus!', response.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message ||
                                'Gagal menghapus tipe pembayaran.', 'error');
                        }.bind(this)
                    });
                }.bind(this));
            });
        });
    </script>
@endpush
