@extends('admin.layouts.app')
@section('title','Reviews')
@section('content')
<div class="page-header"><div><h1>Reviews</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="status" class="form-control"><option value="">Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="hidden">Hidden</option></select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($items as $item)
<tr>
<td>{{ $item->product?->name }}</td><td>{{ $item->customer_name }}</td><td>{{ $item->rating }}</td><td>{{ Str::limit($item->comment,80) }}</td><td>{{ $item->status }}</td>
<td class="actions">
<form method="POST" action="{{ route('admin.reviews.approve',$item) }}">@csrf<button class="btn btn-sm">Approve</button></form>
<form method="POST" action="{{ route('admin.reviews.reject',$item) }}">@csrf<button class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">Reject</button></form>
<form method="POST" action="{{ route('admin.reviews.feature',$item) }}">@csrf<button class="btn btn-sm btn-ghost">Feature</button></form>
<form method="POST" action="{{ route('admin.reviews.hide',$item) }}">@csrf<button class="btn btn-sm btn-ghost">Hide</button></form>
<form method="POST" action="{{ route('admin.reviews.reply',$item) }}">@csrf<input name="admin_reply" class="form-control" placeholder="Reply" required><button class="btn btn-sm btn-secondary">Reply</button></form>
</td></tr>
@empty<tr><td colspan="6">No reviews</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection