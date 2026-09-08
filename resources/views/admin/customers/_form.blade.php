<div class="form-grid">
<div class="form-group"><label>Name *</label><input name="name" class="form-control" value="{{ old('name',$item->name ?? '') }}" required></div>
<div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" value="{{ old('email',$item->email ?? '') }}" required></div>
<div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone',$item->phone ?? '') }}"></div>
<div class="form-group"><label>Group</label><select name="customer_group_id" class="form-control"><option value="">—</option>@foreach($groups as $g)<option value="{{ $g->id }}" @selected(old('customer_group_id',$item->customer_group_id ?? '')==$g->id)>{{ $g->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Company</label><input name="company_name" class="form-control" value="{{ old('company_name',$item->company_name ?? '') }}"></div>
<div class="form-group"><label>GSTIN</label><input name="gstin" class="form-control" value="{{ old('gstin',$item->gstin ?? '') }}"></div>
<div class="form-group"><label>DOB</label><input type="date" name="dob" class="form-control" value="{{ old('dob', optional($item->dob ?? null)->format('Y-m-d')) }}"></div>
<div class="form-group"><label>Gender</label><input name="gender" class="form-control" value="{{ old('gender',$item->gender ?? '') }}"></div>
<div class="form-group"><label>Password</label><input type="password" name="password" class="form-control"></div>
<div class="form-group form-check"><label><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified',$item->is_verified ?? false))> Verified</label></div>
<div class="form-group span-2"><label>Notes</label><textarea name="notes" class="form-control">{{ old('notes',$item->notes ?? '') }}</textarea></div>
</div>