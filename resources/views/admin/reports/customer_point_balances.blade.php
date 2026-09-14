@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Laporan Saldo Poin Customer</h1>
            {{-- <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">Kembali ke Semua Laporan</a> --}}
        </div>

        <!-- Filter Form -->
        <form id="filter-form" class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="period_id" class="form-label">Periode Undian</label>
                <select class="form-select" id="period_id" name="period_id">
                    <option value="">Semua Periode</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }} - {{ $period->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="prize_id" class="form-label">Hadiah</label>
                <select class="form-select" id="prize_id" name="prize_id">
                    <option value="">Semua Hadiah</option>
                    @foreach ($prizes as $prize)
                        <option value="{{ $prize->id }}">{{ $prize->name }} ({{ $prize->rafflePeriod->name ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" id="load-btn" class="btn btn-primary">Load Data</button>
            <button type="button" id="export-btn" class="btn btn-success" style="display: none;">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </div>

        <div class="card shadow-sm">
            <div id="data-table-container" class="table-responsive" style="display: none;">
                <table id="report-table" class="table table-vcenter card-table w-100">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Periode</th>
                            <th>Hadiah</th>
                            <th>Saldo Poin</th>
                            <th>Terakhir Diupdate</th>
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
@endsection

@push('scripts')
    @include('layouts.plugins.datatables', ['ajax_same_page' => false])
    <script>
        window.addEventListener('load', function() {
            var table = null;
            var currentFilters = {};

            $('#load-btn').on('click', function() {
                currentFilters = {
                    period_id: $('#period_id').val(),
                    prize_id: $('#prize_id').val()
                };

                if (!table) {
                    table = $('#report-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route('admin.reports.customer-point-balances.data') }}',
                            data: function(d) {
                                d.period_id = currentFilters.period_id;
                                d.prize_id = currentFilters.prize_id;
                            },
                            error: function(e) {
                                showAlert('error',
                                    e.responseJSON?.message ||
                                    'Terjadi kesalahan saat memuat data. Silakan coba lagi.'
                                );
                            }
                        },
                        columns: [{
                                data: 'customer_name',
                                name: 'customer.name'
                            },
                            {
                                data: 'phone',
                                name: 'customer.phone'
                            },
                            {
                                data: 'email',
                                name: 'customer.email'
                            },
                            {
                                data: 'period_name',
                                name: 'rafflePeriod.name'
                            },
                            {
                                data: 'prize_name',
                                name: 'prize.name'
                            },
                            {
                                data: 'total_poin',
                                name: 'total_poin'
                            },
                            {
                                data: 'updated_at_formatted',
                                name: 'updated_at'
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
                window.location.href = '{{ route('admin.reports.customer-point-balances.export') }}?' +
                    params;
            });
        });
    </script>
@endpush
