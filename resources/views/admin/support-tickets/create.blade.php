@extends('admin.layouts.app')
@section('title','New Ticket')
@section('content')
<div class="page-header"><div><h1>New Ticket</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.support-tickets.store') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Customer</label><select name="customer_id" class="form-control"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Order</label><select name="order_id" class="form-control"><option value="">—</option>@foreach($orders as $o)<option value="{{ $o->id }}">{{ $o->order_number }}</option>@endforeach</select></div>
<div class="form-group"><label>Category</label><select name="category_id" class="form-control"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Priority</label><select name="priority" class="form-control"><option>low</option><option selected>medium</option><option>high</option><option>urgent</option></select></div>
<div class="form-group span-2"><label>Subject</label><input name="subject" class="form-control" required></div>
<div class="form-group span-2"><label>Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
</div>
<button class="btn btn-primary">Create</button></form></div>
@endsection