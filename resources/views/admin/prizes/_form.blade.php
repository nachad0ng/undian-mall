@php
    $prize = $prize ?? null;
    $isUsedInDrawing = $isUsedInDrawing ?? false;
@endphp

<div class="mb-3">
    <label class="form-label required">Periode Undian</label>
    @if ($isUsedInDrawing)
        <input type="hidden" name="raffle_period_id" value="{{ $prize->raffle_period_id }}">
        <input type="text" class="form-control" value="{{ $prize->rafflePeriod?->name }}" disabled>
    @else
        <select name="raffle_period_id" class="form-select @error('raffle_period_id') is-invalid @enderror" required>
            <option value="">Pilih periode</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected(old('raffle_period_id', $prize?->raffle_period_id) == $period->id)>
                    {{ $period->name }} ({{ $period->code }})
                </option>
            @endforeach
        </select>
    @endif
    @error('raffle_period_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required">Nama Hadiah</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $prize?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $prize?->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Jumlah</label>
        <input type="number" name="quantity" min="1" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $prize?->quantity ?? 1) }}" required>
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label required">Urutan</label>
        @if ($isUsedInDrawing)
            <input type="hidden" name="sequence" value="{{ $prize->sequence }}">
            <input type="number" class="form-control" value="{{ $prize->sequence }}" disabled>
        @else
            <input type="number" name="sequence" min="1" class="form-control @error('sequence') is-invalid @enderror" value="{{ old('sequence', $prize?->sequence ?? 1) }}" required>
        @endif
        @error('sequence')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label required">Status</label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
        <option value="active" @selected(old('status', $prize?->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $prize?->status ?? 'active') === 'inactive')>Tidak Aktif</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
