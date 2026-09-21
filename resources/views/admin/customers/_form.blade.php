<div class="collection-form__fields">
    <div class="form-group">
        <label for="customer-name">Name *</label>
        <input id="customer-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required maxlength="255">
        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="customer-email">Email *</label>
        <input id="customer-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $item->email ?? '') }}" required maxlength="255">
        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="customer-phone">Phone</label>
        <input id="customer-phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $item->phone ?? '') }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
        @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="customer-password">Password @if (! empty($item)) <span class="form-hint">(leave blank to keep current)</span> @endif</label>
        <input id="customer-password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
        @if (empty($item))
            <span class="form-hint">Leave blank to create a random password for website login.</span>
        @endif
    </div>
    @if (! empty($item))
        <div class="form-group">
            <label for="customer-status">Status</label>
            <select id="customer-status" name="is_blocked" class="form-control">
                @php $blocked = (string) old('is_blocked', ! empty($item) && $item->is_blocked ? '1' : '0'); @endphp
                <option value="0" @selected($blocked === '0' || $blocked === '')>Active</option>
                <option value="1" @selected($blocked === '1')>Inactive</option>
            </select>
        </div>
    @endif
</div>
