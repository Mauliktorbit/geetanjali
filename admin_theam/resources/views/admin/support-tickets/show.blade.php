@extends('admin.layouts.app')
@section('title',$item->ticket_number)
@section('content')
<div class="page-header"><div><h1>{{ $item->ticket_number }} — {{ $item->subject }}</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<p>{{ $item->priority }} · {{ $item->status }} · {{ $item->customer?->name }} · Assignee {{ $item->assignee?->name }}</p>
@foreach($item->messages as $m)
<div class="mt-2"><strong>{{ $m->user?->name ?? 'System' }}</strong> @if($m->is_internal)<em>(internal)</em>@endif<br>{{ $m->message }}</div>
@endforeach
<form method="POST" action="{{ route('admin.support-tickets.reply',$item) }}" class="mt-3">@csrf
<textarea name="message" class="form-control" required></textarea>
<label class="form-check"><input type="checkbox" name="is_internal" value="1"> Internal note</label>
<button class="btn btn-secondary mt-2">Reply</button></form>
<div class="filters-bar mt-3">
<form method="POST" action="{{ route('admin.support-tickets.assign',$item) }}">@csrf<select name="assigned_to" class="form-control">@foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select><button class="btn btn-ghost">Assign</button></form>
<form method="POST" action="{{ route('admin.support-tickets.escalate',$item) }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Escalate?')">Escalate</button></form>
<form method="POST" action="{{ route('admin.support-tickets.resolve',$item) }}">@csrf<input name="message" class="form-control" placeholder="Resolution note"><button class="btn btn-primary" onclick="return confirm('Resolve?')">Resolve</button></form>
</div></div>
@endsection