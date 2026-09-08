@extends('admin.layouts.app')
@section('title','Add Staff')
@section('content')
<div class="page-header"><div><h1>Add Staff</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.staff.store') }}">@csrf
@include('admin.staff._form')
<button class="btn btn-primary">Create</button></form></div>
@endsection