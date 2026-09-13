@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Audit Log</h2>
                        <div class="text-secondary">Riwayat redemption dan drawing yang tidak dapat diubah.</div>
                    </div>
                </div>

                <form method="GET" class="card mb-3">
                    <div class="card-body row g-3">
                        <div class="col-md-3">
                            <label class="form-label" for="action">Action</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">Semua action</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="user_id">User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Semua user</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="from">Dari</label>
                            <input class="form-control" id="from" name="from" type="date"
                                value="{{ request('from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="to">Sampai</label>
                            <input class="form-control" id="to" name="to" type="date"
                                value="{{ request('to') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.audit-logs.index') }}">Reset</a>
                            <a class="btn btn-success" href="{{ route('admin.audit-logs.export') }}?{{ http_build_query(request()->except('page')) }}">
                                <i class="bi bi-download me-1"></i>Export Excel
                            </a>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Target</th>
                                    <th>Metadata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                        <td><span class="badge bg-blue-lt">{{ $log->action }}</span></td>
                                        <td>{{ $log->user?->name ?? 'System' }}</td>
                                        <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                                        <td><code>{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</code></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">Belum ada audit log.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($logs->hasPages())
                        <div class="card-footer">{{ $logs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
