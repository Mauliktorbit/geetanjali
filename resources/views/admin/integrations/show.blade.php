@extends('admin.layouts.app')
@section('title', 'Integration Details')
@section('content')
<div class="page-header">
    <div>
        <h1>Integration Details</h1>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.integrations.edit', $item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
<div class="card">
    <div class="detail-grid">
        @foreach($item->getAttributes() as $key => $value)
            @if(!in_array($key, ['updated_at']))
            <div class="detail-item">
                <span class="label">{{ ucwords(str_replace('_',' ', $key)) }}</span>
                <span class="value">{{ is_array($value) || is_object($value) ? json_encode($value) : $value }}</span>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection