@extends('admin.layouts.app')
@section('title','Profitability')
@section('content')
<div class="page-header"><div><h1>Profitability</h1></div>
<div class="page-actions"><a href="{{ route('admin.reports.profitability', array_merge(request()->query(),['export'=>'csv'])) }}" class="btn btn-secondary">Export CSV</a></div></div>
<form method="GET" class="filters-bar card"><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"><button class="btn btn-secondary">Run</button></form>
<div class="card"><table class="data-table"><thead><tr><th>Metric</th><th>Amount</th></tr></thead><tbody>
@foreach($rows as $r)<tr><td>{{ $r['metric'] }}</td><td>{{ is_numeric($r['amount']) && !str_contains($r['metric'],'%') ? money($r['amount']) : $r['amount'] }}</td></tr>@endforeach
</tbody></table></div>
@endsection