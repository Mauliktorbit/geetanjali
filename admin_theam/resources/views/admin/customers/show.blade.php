@extends('admin.layouts.app')
@section('title',$item->name)
@section('content')
<div class="page-header"><div><h1>{{ $item->name }}</h1></div>
<div class="page-actions">
<a href="{{ route('admin.customers.edit',$item) }}" class="btn btn-primary">Edit</a>
@if($item->is_blocked)
<form method="POST" action="{{ route('admin.customers.unblock',$item) }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Unblock?')">Unblock</button></form>
@else
<form method="POST" action="{{ route('admin.customers.block',$item) }}">@csrf<input name="reason" class="form-control" placeholder="Reason"><button class="btn btn-danger" onclick="return confirm('Block?')">Block</button></form>
@endif
</div></div>
@include('admin.components.alerts')
<div class="card"><p>{{ $item->email }} · {{ $item->phone }} · Group: {{ $item->group?->name }}</p>
<p>Wallet {{ money($item->wallet_balance) }} · Points {{ $item->reward_points }} · Orders {{ $item->total_orders }} · Spent {{ money($item->total_spent) }}</p>
<div class="filters-bar">
<form method="POST" action="{{ route('admin.customers.reset-password',$item) }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Reset password?')">Reset password</button></form>
<form method="POST" action="{{ route('admin.customers.assign-group',$item) }}">@csrf<select name="customer_group_id" class="form-control">@foreach($groups as $g)<option value="{{ $g->id }}" @selected($item->customer_group_id==$g->id)>{{ $g->name }}</option>@endforeach</select><button class="btn btn-ghost">Assign group</button></form>
<form method="POST" action="{{ route('admin.customers.store-credit',$item) }}">@csrf<input type="number" step="0.01" name="amount" class="form-control" placeholder="Credit +/-" required><input name="reason" class="form-control" placeholder="Reason"><button class="btn btn-secondary">Wallet</button></form>
<form method="POST" action="{{ route('admin.customers.reward-points',$item) }}">@csrf<input type="number" name="points" class="form-control" placeholder="Points +/-" required><button class="btn btn-secondary">Points</button></form>
</div>
<form method="POST" action="{{ route('admin.customers.merge',$item) }}" class="filters-bar mt-2">@csrf
<select name="secondary_id" class="form-control" required>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select>
<button class="btn btn-danger" onclick="return confirm('Merge selected into this customer?')">Merge</button>
</form>
</div>
<div class="grid-2 mt-4">
<div class="card"><h3>Orders</h3><ul>@forelse($item->orders as $o)<li><a href="{{ route('admin.orders.show',$o) }}">{{ $o->order_number }}</a> — {{ money($o->grand_total) }}</li>@empty<li>None</li>@endforelse</ul></div>
<div class="card"><h3>Addresses</h3><ul>@forelse($item->addresses as $a)<li>{{ $a->address_line1 ?? '' }} {{ $a->city ?? '' }} {{ $a->pincode ?? '' }}</li>@empty<li>None</li>@endforelse</ul>
<h3>Wishlist</h3><ul>@forelse($item->wishlists as $w)<li>{{ $w->product?->name }}</li>@empty<li>None</li>@endforelse</ul></div>
<div class="card"><h3>Wallet</h3><ul>@forelse($item->walletTransactions as $t)<li>{{ $t->type }} {{ money($t->amount) }} — {{ $t->reason }}</li>@empty<li>None</li>@endforelse</ul>
<h3>Points</h3><ul>@forelse($item->rewardPointTransactions as $t)<li>{{ $t->type }} {{ $t->points }} — {{ $t->reason }}</li>@empty<li>None</li>@endforelse</ul></div>
<div class="card"><h3>Notes</h3>
<form method="POST" action="{{ route('admin.customers.notes',$item) }}">@csrf<textarea name="note" class="form-control" required></textarea><button class="btn btn-secondary mt-2">Add note</button></form>
@foreach($item->customerNotes as $n)<p><em>{{ $n->user?->name }}</em>: {{ $n->note }}</p>@endforeach
</div></div>
@endsection