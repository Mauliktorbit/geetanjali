<div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name',$item->name ?? '') }}" required></div>
<h3>Permissions</h3>
@foreach($permissions as $group=>$perms)
<div class="card mt-2"><strong>{{ $group }}</strong>
@foreach($perms as $perm)
<label class="form-check"><input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked(collect(old('permissions',$rolePermissions ?? []))->contains($perm->name))> {{ $perm->name }}</label>
@endforeach
</div>
@endforeach