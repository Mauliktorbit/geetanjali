@extends('admin.layouts.app')
@section('title','Abandoned Cart')
@section('content')
<div class="page-header"><div><h1>Abandoned Cart #{{ $item->id }}</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<p>{{ $item->email }} · {{ $item->phone }} · {{ money($item->cart_value) }} · {{ $item->recovery_status }}</p>
<pre>{{ json_encode($item->cart_items, JSON_PRETTY_PRINT) }}</pre>
<form method="POST" action="{{ route('admin.abandoned-carts.remind',$item) }}" class="filters-bar">@csrf
<select name="channel" class="form-control"><option value="email">Email</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option><option value="push">Push</option></select>
<input name="subject" class="form-control" placeholder="Subject">
<textarea name="message" class="form-control" rows="2" placeholder="Custom message"></textarea>
<button class="btn btn-primary" onclick="return confirm('Send reminder?')">Send reminder</button>
</form></div>
@endsection