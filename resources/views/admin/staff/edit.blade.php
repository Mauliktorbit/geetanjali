@extends('admin.layouts.app')
@section('title','Edit Staff')
@section('content')
<div class="page-header"><div><h1>Edit Staff</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.staff.update',$item) }}">@csrf @method('PUT')
@include('admin.staff._form')
<button class="btn btn-primary">Update</button></form></div>
@endsection