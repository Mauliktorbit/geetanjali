@extends('admin.layouts.app')
@section('title','Product Questions')
@section('content')
<div class="page-header"><div><h1>Product Questions</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="status" class="form-control"><option value="">Status</option><option value="pending">Pending</option><option value="answered">Answered</option><option value="published">Published</option><option value="hidden">Hidden</option></select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>Product</th><th>Question</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->product?->name }}</td><td>{{ $item->question }}<br><small>{{ $item->answer }}</small></td><td>{{ $item->status }}</td>
<td>
<form method="POST" action="{{ route('admin.product-questions.answer',$item) }}">@csrf<textarea name="answer" class="form-control" required>{{ $item->answer }}</textarea><button class="btn btn-sm btn-secondary">Answer</button></form>
<form method="POST" action="{{ route('admin.product-questions.publish',$item) }}">@csrf<button class="btn btn-sm">Publish</button></form>
<form method="POST" action="{{ route('admin.product-questions.hide',$item) }}">@csrf<button class="btn btn-sm btn-ghost">Hide</button></form>
<form method="POST" action="{{ route('admin.product-questions.assign',$item) }}">@csrf<select name="assigned_to" class="form-control">@foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select><button class="btn btn-sm">Assign</button></form>
</td></tr>
@empty<tr><td colspan="4">No questions</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection