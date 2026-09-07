@extends('layouts.app')

@section('title', 'Manage Roles')

@push('styles')
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manage Roles</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Create Role
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table id="table" class="table table-vcenter card-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Permissions</th>
                                <th>Created At</th>
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
    const table = $('#table').DataTable({
        columns: [
            {
                data: 'name',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: 'permissions_count',
                render: function (data) {
                    return `<span class="badge bg-blue text-blue-fg">${data} permissions</span>`;
                },
                searchable: false,
            },
            {
                data: 'created_at',
                searchable: false,
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${row.actions.edit_url}" class="btn btn-icon btn-ghost-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button
                                type="button"
                                class="btn btn-icon btn-ghost-danger btn-delete"
                                title="Delete"
                                data-delete-url="${row.actions.delete_url}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            },
        ],
    });

    // Konfirmasi delete via modal
    table.on('click', '.btn-delete', function () {
        const deleteUrl = $(this).data('delete-url');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function () {
                        table.ajax.reload(null, false);
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    },
                    error: function (xhr) {
                        const res = xhr.responseJSON;
                        showAlert('error', res?.message ?? 'Data gagal dihapus.', 'error');
                    }
                });
            }
        });
    });
</script>
@endpush
