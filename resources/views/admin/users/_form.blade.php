@php $user = $user ?? null; @endphp

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label required">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user?->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user?->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Telepon</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $user?->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $user?->address) }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label {{ $user ? '' : 'required' }}">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                {{ $user ? '' : 'required' }}>
            @if ($user)
                <div class="form-hint">Kosongkan jika tidak ingin mengubah password.</div>
            @endif
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label {{ $user ? '' : 'required' }}">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" {{ $user ? '' : 'required' }}>
        </div>
        <div class="mb-3">
            <label class="form-label required">Role</label>
            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Pilih role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $user?->roles->first()?->name) === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label required">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" @selected(old('status', $user?->status ?? 'active') === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $user?->status ?? 'active') === 'inactive')>Tidak Aktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
