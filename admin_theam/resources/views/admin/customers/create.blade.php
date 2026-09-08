@extends('admin.layouts.app')
@section('title','Add Customer')
@section('content')
<div class="page-header"><div><h1>Add Customer</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.customers.store') }}">@csrf
@include('admin.customers._form')
<div class="form-actions"><button class="btn btn-primary">Save</button></div></form></div>
@endsection