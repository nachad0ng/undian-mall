@extends('layouts.app')

@section('title', 'Daftar Pemenang')

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                <div class="row mb-3 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Daftar Pemenang</h2>
                        <div class="text-secondary">Pemenang resmi yang telah dipublikasikan.</div>
                    </div>
                </div>

                <form method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="visually-hidden" for="period_id">Periode</label>
                            <select class="form-select" id="period_id" name="period_id" onchange="this.form.submit()">
                                <option value="">Semua periode</option>
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}" @selected((string) request('period_id') === (string) $period->id)>{{ $period->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <div class="row row-cards">
                    @forelse ($winners as $winner)
                        <div class="col-sm-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="text-secondary small">{{ $winner->rafflePeriod->name }}</div>
                                    <h3 class="card-title mt-1">{{ $winner->customer->name }}</h3>
                                    <div>{{ $winner->prize->name }}</div>
                                    <div class="text-secondary small mt-2">{{ $winner->won_at->format('d M Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty">
                                <p class="empty-title">Belum ada pemenang yang dipublikasikan.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($winners->hasPages())
                    <div class="mt-3">{{ $winners->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
