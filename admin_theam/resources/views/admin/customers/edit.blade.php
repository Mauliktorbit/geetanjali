@extends('admin.layouts.app')
@section('title','Edit Customer')
@section('content')
<div class="page-header"><div><h1>Edit Customer</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.customers.update',$item) }}">@csrf @method('PUT')
@include('admin.customers._form')
<div class="form-actions"><button class="btn btn-primary">Update</button></div></form></div>
@endsection