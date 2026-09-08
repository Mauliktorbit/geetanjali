@extends('admin.layouts.app')
@section('title','Sales Report')
@section('content')
<div class="page-header"><div><h1>Sales Report</h1></div>
<div class="page-actions"><a href="{{ route('admin.reports.sales', array_merge(request()->query(),['export'=>'csv'])) }}" class="btn btn-secondary">Export CSV</a></div></div>
<form method="GET" class="filters-bar card"><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"><select name="group_by" class="form-control"><option value="day">Day</option><option value="week">Week</option><option value="month">Month</option></select><button class="btn btn-secondary">Run</button></form>
<div class="card"><table class="data-table"><thead><tr><th>Period</th><th>Orders</th><th>Revenue</th><th>Cost</th><th>Profit</th></tr></thead><tbody>
@forelse($rows as $r)<tr><td>{{ $r->period }}</td><td>{{ $r->orders }}</td><td>{{ money($r->revenue) }}</td><td>{{ money($r->cost) }}</td><td>{{ money($r->profit) }}</td></tr>@empty<tr><td colspan="5">No data</td></tr>@endforelse
</tbody></table></div>
@endsection