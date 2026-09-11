@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Manage Users</h2>
                    </div>
                    <div class="col-auto"><a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i
                                class="bi bi-plus-lg"></i> Tambah User</a></div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table id="table" class="table table-vcenter card-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="w-1">Actions</th>
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
                searching: false,
                dom: "<'card-header d-flex align-items-center justify-content-between'l><'table-responsive't><'card-footer d-flex align-items-center justify-content-between'i<'ms-auto'p>>",
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'role_name',
                        searchable: false
                    },
                    {
                        data: 'status',
                        searchable: false,
                        render: data => data === 'Aktif' ?
                            '<span class="badge bg-green-lt">Aktif</span>' :
                            '<span class="badge bg-secondary-lt">Tidak Aktif</span>'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const deleteButton = row.actions.is_current ? '' :
                                `<button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete" title="Hapus" data-delete-url="${row.actions.delete_url}"><i class="bi bi-trash"></i></button>`;
                            return `<div class="btn-group"><a href="${row.actions.show_url}" class="btn btn-sm btn-icon btn-ghost-secondary" title="Lihat"><i class="bi bi-eye"></i></a><a href="${row.actions.edit_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>${deleteButton}</div>`;
                        }
                    }
                ]
            });

            table.on('click', '.btn-delete', function() {
                const deleteUrl = $(this).data('delete-url');
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus user ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus!', response.message, 'success');
                        },
                        error: function(xhr) {
                            showAlert('error', xhr.responseJSON?.message ??
                                'User gagal dihapus.', 'error');
                        }
                    });
                });
            });
        });
    </script>
@endpush
