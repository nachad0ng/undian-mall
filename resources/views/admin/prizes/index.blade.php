@extends('layouts.app')

@section('title', 'Hadiah')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Hadiah</h2>
                        <div class="text-secondary mt-1">Kelola hadiah, urutan undian, dan jumlah per periode</div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.prizes.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Hadiah
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <form class="d-flex flex-wrap gap-2 w-100" onsubmit="return false;">
                            <input type="text" id="filter-search" class="form-control" style="max-width: 250px;"
                                placeholder="Cari nama hadiah...">
                            <select id="filter-period" class="form-select" style="max-width: 250px;"
                                data-default="{{ $defaultPeriodId }}">
                                <option value="">Semua Periode</option>
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}"
                                        {{ (string) $defaultPeriodId === (string) $period->id ? 'selected' : '' }}>
                                        {{ $period->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="filter-apply" class="btn btn-outline-primary">Filter</button>
                            <button type="button" id="filter-reset" class="btn btn-outline-secondary">Reset</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="prizes-table" class="table table-vcenter card-table w-100">
                            <thead>
                                <tr>
                                    <th>Urutan</th>
                                    <th>Periode</th>
                                    <th>Hadiah</th>
                                    <th>Jumlah</th>
                                    <th>Pemenang</th>
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
            const $periodSelect = $('#filter-period');
            const $search = $('#filter-search');
            const isAllPeriods = () => $periodSelect.val() === '';
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const table = $('#prizes-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                dom: "t<'card-footer d-flex align-items-center justify-content-between'i<'ms-auto'p>>",
                ajax: {
                    url: @json(route('admin.prizes.index')),
                    data: function(d) {
                        d.raffle_period_id = $periodSelect.val();
                        d.search = $search.val();
                    }
                },
                columns: [{
                        data: 'urutan',
                        name: 'sequence',
                        className: 'text-center',
                        visible: {{ $defaultPeriodId ? 'true' : 'false' }},
                        render: function(data) {
                            const disabled = data.is_used_in_drawing ? 'disabled' : '';
                            return `
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary btn-move"
                                data-url="${data.move_up_url}" data-direction="up" title="Naikkan urutan" ${disabled}>
                                <i class="bi bi-chevron-up"></i>
                            </button>
                            <span class="mx-1">${data.sequence}</span>
                            <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary btn-move"
                                data-url="${data.move_down_url}" data-direction="down" title="Turunkan urutan" ${disabled}>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>`;
                        }
                    },
                    {
                        data: 'period_name',
                        name: 'period_name',
                        visible: {{ $defaultPeriodId ? 'false' : 'true' }}
                    },
                    {
                        data: null,
                        name: 'name',
                        render: (row) =>
                            `<a href="${row.actions.show_url}" class="text-reset">${row.name}</a>`
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'winners_count',
                        name: 'winners_count',
                        className: 'text-center',
                        render: (data) => `<span class="badge bg-blue-lt">${data}</span>`
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
                        name: 'actions',
                        className: 'text-center',
                        orderable: false,
                        render: (data) => `
                    <div class="btn-group">
                        <a href="${data.show_url}" class="btn btn-sm btn-icon btn-ghost-primary" title="Detail"><i class="bi bi-eye"></i></a>
                        <a href="${data.edit_url}" class="btn btn-sm btn-icon btn-ghost-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete" data-url="${data.delete_url}" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>`
                    }
                ],
                language: {
                    emptyTable: 'Belum ada data hadiah.',
                    processing: 'Memuat...'
                }
            });

            function toggleColumns() {
                const allPeriods = isAllPeriods();
                table.column(0).visible(!allPeriods, false);
                table.column(1).visible(allPeriods, false);
                table.columns.adjust();
            }

            $periodSelect.on('change', function() {
                toggleColumns();
                table.ajax.reload();
            });

            $('#filter-apply').on('click', () => table.ajax.reload());
            $search.on('keypress', (e) => {
                if (e.which === 13) table.ajax.reload();
            });

            $('#filter-reset').on('click', function() {
                $search.val('');
                $periodSelect.val($periodSelect.data('default') || '');
                toggleColumns();
                table.ajax.reload();
            });

            $('#prizes-table').on('click', '.btn-move', function() {
                const $btn = $(this);
                if ($btn.is(':disabled')) return;

                $.ajax({
                    url: $btn.data('url'),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        direction: $btn.data('direction')
                    },
                    success: () => table.ajax.reload(null, false),
                    error: (xhr) => alert(xhr.responseJSON?.message || 'Gagal memperbarui urutan.')
                });
            });

            $('#prizes-table').on('click', '.btn-delete', function() {
                if (!confirm('Yakin ingin menghapus hadiah ini?')) return;

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
                    error: (xhr) => alert(xhr.responseJSON?.message || 'Gagal menghapus hadiah.')
                });
            });
        });
    </script>
@endpush
