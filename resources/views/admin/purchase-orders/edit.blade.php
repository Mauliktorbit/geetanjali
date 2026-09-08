@extends('admin.layouts.app')
@section('title','Edit PO')
@section('content')
<div class="page-header"><div><h1>Edit {{ $item->po_number }}</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.purchase-orders.update',$item) }}">@csrf @method('PUT')
@include('admin.purchase-orders._form')
<button class="btn btn-primary">Update</button></form></div>
@endsection