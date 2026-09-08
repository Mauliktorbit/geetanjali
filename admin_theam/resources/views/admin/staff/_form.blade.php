<div class="form-grid">
<div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name',$item->name ?? '') }}" required></div>
<div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$item->email ?? '') }}" required></div>
<div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone',$item->phone ?? '') }}"></div>
<div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" {{ isset($item)?'':'required' }}></div>
<div class="form-group"><label>Confirm password</label><input type="password" name="password_confirmation" class="form-control"></div>
<div class="form-group"><label>Allowed IPs (comma)</label><input name="allowed_ips" class="form-control" value="{{ old('allowed_ips', isset($item) && $item->allowed_ips ? implode(', ',$item->allowed_ips) : '') }}"></div>
<div class="form-group form-check"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$item->is_active ?? true))> Active</label></div>
<div class="form-group span-2"><label>Roles</label>
@foreach($roles as $role)
<label class="form-check"><input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(collect(old('roles', isset($item)?$item->roles->pluck('name')->all():[]))->contains($role->name))> {{ $role->name }}</label>
@endforeach
</div></div>