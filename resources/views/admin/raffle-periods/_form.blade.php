@php
    $period = $period ?? null;
    $startAt = old('start_at', $period?->start_at?->format('Y-m-d\TH:i'));
    $endAt = old('end_at', $period?->end_at?->format('Y-m-d\TH:i'));
@endphp

<div class="mb-3">
    <label class="form-label required">Kode</label>
    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $period?->code) }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required">Nama Periode</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $period?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $period?->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Mulai</label>
        <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ $startAt }}" required>
        @error('start_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label required">Berakhir</label>
        <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ $endAt }}" required>
        @error('end_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="hr-text">Aturan Kupon</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Minimum Belanja (Rp)</label>
        <input type="number" name="purchase_threshold" min="1" class="form-control @error('purchase_threshold') is-invalid @enderror" value="{{ old('purchase_threshold', $period?->purchase_threshold) }}" required>
        @error('purchase_threshold')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label required">Kelipatan per Kupon (Rp)</label>
        <input type="number" name="coupon_unit" min="1" class="form-control @error('coupon_unit') is-invalid @enderror" value="{{ old('coupon_unit', $period?->coupon_unit) }}" required>
        @error('coupon_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Maks. Kupon / Transaksi</label>
        <input type="number" name="max_coupon_per_transaction" min="1" class="form-control @error('max_coupon_per_transaction') is-invalid @enderror" value="{{ old('max_coupon_per_transaction', $period?->max_coupon_per_transaction) }}">
        <small class="form-hint">Kosongkan jika tidak dibatasi</small>
        @error('max_coupon_per_transaction')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label required">Status</label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
        @foreach (['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'closed' => 'Ditutup'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $period?->status ?? 'draft') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
