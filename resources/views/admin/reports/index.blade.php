@extends('admin.layouts.app')
@section('title','Reports')
@section('content')
<div class="page-header"><div><h1>Reports</h1></div></div>
<div class="grid-2">
<a class="card" href="{{ route('admin.reports.sales') }}"><h3>Sales</h3><p>Revenue by period</p></a>
<a class="card" href="{{ route('admin.reports.products') }}"><h3>Products</h3><p>Units & profitability</p></a>
<a class="card" href="{{ route('admin.reports.customers') }}"><h3>Customers</h3><p>Spend & orders</p></a>
<a class="card" href="{{ route('admin.reports.profitability') }}"><h3>Profitability</h3><p>Margins & expenses</p></a>
</div>
@endsection