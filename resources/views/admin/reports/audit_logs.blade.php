@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Laporan Audit Log</h1>
            {{-- <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">Kembali ke Semua Laporan</a> --}}
        </div>

        <!-- Filter Form -->
        <form id="filter-form" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="action" class="form-label">Aksi</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Semua Aksi</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}">{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="user_id" class="form-label">User</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Semua User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="from" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="from" name="from">
            </div>
            <div class="col-md-2">
                <label for="to" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="to" name="to">
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" id="load-btn" class="btn btn-primary">Load Data</button>
            <button type="button" id="export-btn" class="btn btn-success" style="display: none;">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </div>

        <div class="card">
            <div id="data-table-container" class="table-responsive" style="display: none;">
                <table id="report-table" class="table table-vcenter card-table w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Entitas</th>
                            <th>Metadata</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="empty-placeholder" class="text-center py-5 text-muted">
                <i class="fas fa-table fa-2x mb-2"></i>
                <p>Klik tombol "Load Data" untuk menampilkan laporan.</p>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('layouts.plugins.datatables', ['ajax_same_page' => false])
        <script>
            window.addEventListener('load', function() {
                var table = null;
                var currentFilters = {};

                $('#load-btn').on('click', function() {
                    currentFilters = {
                        action: $('#action').val(),
                        user_id: $('#user_id').val(),
                        from: $('#from').val(),
                        to: $('#to').val()
                    };

                    if (!table) {
                        table = $('#report-table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route('admin.reports.audit-logs.data') }}',
                                data: function(d) {
                                    d.action = currentFilters.action;
                                    d.user_id = currentFilters.user_id;
                                    d.from = currentFilters.from;
                                    d.to = currentFilters.to;
                                }
                            },
                            columns: [{
                                    data: 'created_at_formatted',
                                    name: 'created_at'
                                },
                                {
                                    data: 'user_name',
                                    name: 'user.name'
                                },
                                {
                                    data: 'action',
                                    name: 'action'
                                },
                                {
                                    data: 'auditable',
                                    name: 'auditable_type'
                                },
                                {
                                    data: 'metadata_json',
                                    name: 'metadata'
                                }
                            ],
                            language: {
                                emptyTable: 'Tidak ada data ditemukan.'
                            }
                        });
                        $('#export-btn').show();
                    } else {
                        table.ajax.reload();
                    }

                    $('#data-table-container').show();
                    $('#empty-placeholder').hide();
                });

                $('#export-btn').on('click', function() {
                    var params = $.param(currentFilters);
                    window.location.href = '{{ route('admin.reports.audit-logs.export') }}?' + params;
                });
            });
        </script>
    @endpush
@endsection
