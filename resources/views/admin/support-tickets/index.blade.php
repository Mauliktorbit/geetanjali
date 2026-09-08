@extends('admin.layouts.app')
@section('title','Support Tickets')
@section('content')
<div class="page-header"><div><h1>Support Tickets</h1></div><div class="page-actions"><a href="{{ route('admin.support-tickets.create') }}" class="btn btn-primary">New ticket</a></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>Ticket</th><th>Subject</th><th>Priority</th><th>Status</th><th>Assignee</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->ticket_number }}</td><td>{{ $item->subject }}</td><td>{{ $item->priority }}</td><td>{{ $item->status }}</td><td>{{ $item->assignee?->name }}</td>
<td><a href="{{ route('admin.support-tickets.show',$item) }}" class="btn btn-sm">Open</a></td></tr>
@empty<tr><td colspan="6">None</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection