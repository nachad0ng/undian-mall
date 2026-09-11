@csrf
<div class="mb-3">
    <label class="form-label required">Hadiah</label><select name="prize_id"
        class="form-select @error('prize_id') is-invalid @enderror" required>
        <option value="">Pilih hadiah</option>
        @foreach ($prizes as $prize)
            <option value="{{ $prize->id }}" @selected(old('prize_id', $pointRule->prize_id ?? '') == $prize->id)>{{ $prize->name }} -
                {{ $prize->rafflePeriod?->name }}</option>
        @endforeach
    </select>
    @error('prize_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3"><label class="form-label required">Nominal per poin</label><input type="number"
        name="nominal_per_poin" min="1" class="form-control @error('nominal_per_poin') is-invalid @enderror"
        value="{{ old('nominal_per_poin', $pointRule->nominal_per_poin ?? '') }}" required>
    <div class="form-hint">Nominal belanja yang menghasilkan 1 poin.</div>
    @error('nominal_per_poin')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="row">
    <div class="col-md-6 mb-3"><label class="form-label required">Berlaku mulai</label><input type="datetime-local"
            name="berlaku_mulai" class="form-control @error('berlaku_mulai') is-invalid @enderror"
            value="{{ old('berlaku_mulai', isset($pointRule) ? $pointRule->berlaku_mulai?->format('Y-m-d\TH:i') : '') }}"
            required>
        @error('berlaku_mulai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3"><label class="form-label">Berlaku sampai</label><input type="datetime-local"
            name="berlaku_sampai" class="form-control @error('berlaku_sampai') is-invalid @enderror"
            value="{{ old('berlaku_sampai', isset($pointRule) ? $pointRule->berlaku_sampai?->format('Y-m-d\TH:i') : '') }}">
        @error('berlaku_sampai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="mb-3"><label class="form-label">Keterangan</label><input type="text" name="keterangan"
        class="form-control @error('keterangan') is-invalid @enderror"
        value="{{ old('keterangan', $pointRule->keterangan ?? '') }}">
    @error('keterangan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3"><label class="form-label required">Status</label><select name="is_active" class="form-select"
        required>
        <option value="1" @selected(old('is_active', $pointRule->is_active ?? true) == 1)>Aktif</option>
        <option value="0" @selected(old('is_active', $pointRule->is_active ?? true) == 0)>Nonaktif</option>
    </select></div>
