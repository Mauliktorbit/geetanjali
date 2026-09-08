@extends('admin.layouts.app')
@section('title','Customer Report')
@section('content')
<div class="page-header"><div><h1>Customer Report</h1></div>
<div class="page-actions"><a href="{{ route('admin.reports.customers', array_merge(request()->query(),['export'=>'csv'])) }}" class="btn btn-secondary">Export CSV</a></div></div>
<form method="GET" class="filters-bar card"><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"><button class="btn btn-secondary">Run</button></form>
<div class="card"><table class="data-table"><thead><tr><th>Customer</th><th>Email</th><th>Period orders</th><th>Period spent</th><th>Lifetime spent</th></tr></thead><tbody>
@forelse($rows as $r)<tr><td>{{ $r->name }}</td><td>{{ $r->email }}</td><td>{{ $r->period_orders }}</td><td>{{ money($r->period_spent) }}</td><td>{{ money($r->total_spent) }}</td></tr>@empty<tr><td colspan="5">No data</td></tr>@endforelse
</tbody></table></div>
@endsection