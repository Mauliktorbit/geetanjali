@extends('admin.layouts.app')
@section('title','Create PO')
@section('content')
<div class="page-header"><div><h1>Create Purchase Order</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.purchase-orders.store') }}">@csrf
@include('admin.purchase-orders._form')
<button class="btn btn-primary">Save</button></form></div>
@endsection