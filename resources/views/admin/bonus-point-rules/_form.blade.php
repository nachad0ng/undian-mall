@csrf
<div class="mb-3">
    <label class="form-label required">Periode undian</label><select name="raffle_period_id"
        class="form-select @error('raffle_period_id') is-invalid @enderror" required>
        <option value="">Pilih periode</option>
        @foreach ($periods as $period)
            <option value="{{ $period->id }}" @selected(old('raffle_period_id', $bonusPointRule->raffle_period_id ?? '') == $period->id)>{{ $period->name }}</option>
        @endforeach
    </select>
    @error('raffle_period_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3"><label class="form-label required">Tipe pembayaran</label><select name="payment_type_id"
        class="form-select @error('payment_type_id') is-invalid @enderror" required>
        <option value="">Pilih tipe pembayaran</option>
        @foreach ($paymentTypes as $paymentType)
            <option value="{{ $paymentType->id }}" @selected(old('payment_type_id', $bonusPointRule->payment_type_id ?? '') == $paymentType->id)>{{ $paymentType->name }}
                ({{ $paymentType->code }})</option>
        @endforeach
    </select>
    @error('payment_type_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3"><label class="form-label required">Bonus poin</label><input type="number" name="bonus_poin"
        min="0" class="form-control @error('bonus_poin') is-invalid @enderror"
        value="{{ old('bonus_poin', $bonusPointRule->bonus_poin ?? 0) }}" required>
    @error('bonus_poin')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3"><label class="form-label required">Status</label><select name="is_active" class="form-select"
        required>
        <option value="1" @selected(old('is_active', $bonusPointRule->is_active ?? true) == 1)>Aktif</option>
        <option value="0" @selected(old('is_active', $bonusPointRule->is_active ?? true) == 0)>Nonaktif</option>
    </select></div>
