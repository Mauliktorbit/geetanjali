@extends('admin.layouts.app')
@section('title','Edit Role')
@section('content')
<div class="page-header"><div><h1>Edit Role</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.roles.update',$item) }}">@csrf @method('PUT')
@include('admin.roles._form')
<button class="btn btn-primary">Update</button></form></div>
@endsection