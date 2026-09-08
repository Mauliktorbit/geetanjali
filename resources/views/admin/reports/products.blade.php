@extends('admin.layouts.app')
@section('title','Product Report')
@section('content')
<div class="page-header"><div><h1>Product Report</h1></div>
<div class="page-actions"><a href="{{ route('admin.reports.products', array_merge(request()->query(),['export'=>'csv'])) }}" class="btn btn-secondary">Export CSV</a></div></div>
<form method="GET" class="filters-bar card"><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"><button class="btn btn-secondary">Run</button></form>
<div class="card"><table class="data-table"><thead><tr><th>Product</th><th>SKU</th><th>Units</th><th>Revenue</th><th>Profit</th></tr></thead><tbody>
@forelse($rows as $r)<tr><td>{{ $r->product_name }}</td><td>{{ $r->sku }}</td><td>{{ $r->units_sold }}</td><td>{{ money($r->revenue) }}</td><td>{{ money($r->profit) }}</td></tr>@empty<tr><td colspan="5">No data</td></tr>@endforelse
</tbody></table></div>
@endsection