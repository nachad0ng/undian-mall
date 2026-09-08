@php $tenant = $tenant ?? null; @endphp

<div class="mb-3">
    <label class="form-label required">Kode</label>
    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $tenant?->code) }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required">Nama Tenant</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tenant?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Nomor Unit</label>
    <input type="text" name="unit_number" class="form-control @error('unit_number') is-invalid @enderror" value="{{ old('unit_number', $tenant?->unit_number) }}">
    @error('unit_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Telepon</label>
    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $tenant?->phone) }}">
    @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required">Status</label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
        <option value="active" @selected(old('status', $tenant?->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $tenant?->status ?? 'active') === 'inactive')>Tidak Aktif</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
