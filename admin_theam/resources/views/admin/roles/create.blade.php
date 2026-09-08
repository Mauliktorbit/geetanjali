@extends('admin.layouts.app')
@section('title','Create Role')
@section('content')
<div class="page-header"><div><h1>Create Role</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.roles.store') }}">@csrf
@include('admin.roles._form',['rolePermissions'=>[]])
<button class="btn btn-primary">Create</button></form></div>
@endsection